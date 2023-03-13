<?php

namespace SeaportAcmeTicketing;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Exception;
use WP_Post;

class PostMeta {
    protected Database $database;

    public function __construct()
    {
        $this->database = new Database();
    }

    /**
     * Gets the posts linked to the provided template and updates all the custom fields
     *
     * @param object $template
     * @return int
     */
    public function updatePostsByTemplate(object $template): int
    {
        $updateCount = 0;

        //find posts by template ID
        $posts = $this->database->getLinkedPostsByTemplateID($template->id);

        /** @var WP_Post $post */
        foreach ($posts as $post)
        {
            $this->updatePostCustomFields($post, $template);
            $updateCount++;
        }

        return $updateCount;
    }

    public function updatePostCustomFields(WP_Post $post, object $template): void
    {
        if (! Database::isSyncActive()) {
            return;
        }

        //set the event description
        $this->updatePostDescription($post, $template);
        //set the event short description
        $this->updatePostShortDescription( $post, $template);
        //set the event title
        $this->updatePostTitle($post, $template);
        //set the Book Now URL
        $this->updatePostBookUrl($post, $template);
        //add the event description shortcode
        $this->addEventDescriptionShortcodeToLinkedPost($post);
        //add the hide event text custom field, defaulting to "N" if not already set
        $this->updatePostHideData($post);

        //this take a while, so check if we are still active
        if (! Database::isSyncActive()) {
            return;
        }

        //set the active event calendar dates
        $this->updatePostEventDates($post, $template);
        //add a Tag for "This Week" if the post's event occurs at least once during the current week
        $this->setTagThisWeek($post, $template);
    }

    /**
     * Add the "hide event text" custom field, defaulting to "N".
     * If the custom field already exists and is set to "Y", skip so not to reset the user preference
     *
     * @param WP_Post $post
     *
     * @return void
     */
    public function updatePostHideData(WP_Post $post): void
    {
        $hide = get_post_meta( $post->ID, Constants::CUSTOM_FIELD_HIDE_EVENT_TEXT, true );

        if ($hide != 'Y') {
            $this->setCustomField($post, Constants::CUSTOM_FIELD_HIDE_EVENT_TEXT, 'N');
        }
    }

    public function updatePostTitle(WP_Post $post, object $template): void
    {
        $this->setCustomField($post, Constants::CUSTOM_FIELD_EVENT_TITLE, $template->name);
    }

    public function updatePostShortDescription(WP_Post $post, object $template): void
    {
        $this->setCustomField($post, Constants::CUSTOM_FIELD_EVENT_SHORT_DESC, $template->short_description);
    }

    public function updatePostDescription(WP_Post $post, object $template): void
    {
        $this->setCustomField($post, Constants::CUSTOM_FIELD_EVENT_DESC, $template->description);
    }

    public function updatePostEventDates(WP_Post $post, object $template): void
    {
        $data = $this->database->getCalenderByTemplateID( $template->id, Carbon::today(), Carbon::today()->addMonths( 3));
        $dates = [];

        foreach ($data as $row) {
            $dates[] = $row->event_date;
        }

        $value = implode("\n", $dates);
        $this->setCustomField($post, Constants::CUSTOM_FIELD_EVENT_DATES, $value);
    }

    /**
     * Set's the "Book Now" full URL. This prevents the template from needing to understand any specific logic
     *
     * @param WP_Post $post
     * @param object $template
     *
     * @return void
     */
    public function updatePostBookUrl(WP_Post $post, object $template): void
    {
        $count = $this->database->getEventCountByTemplateId($template->id);

        if ($count > 1) {
            $url = (new AcmeUrl())->getEventDetailUrl( $template->id );
        } else {
            $url = (new AcmeUrl())->getEventCartPageForToday($template->id);
        }

        try {
            $this->setCustomField( $post, Constants::CUSTOM_FIELD_BOOK_NOW, $url );
        } catch (Exception $exception) {
            Log::exception($exception);
        }
    }

    /**
     * Updates or Creates a new custom field with the given value
     * We store data in the custom fields so the template does not have to be aware of the Acme Plugin
     * This also makes it possible for end-users to harness the template's display functionality for non-Acme events
     *
     * @param WP_Post $post
     * @param string $field
     * @param string $value
     *
     * @return void
     */
    public function setCustomField(WP_Post $post, string $field, string $value): void
    {
        update_post_meta($post->ID, $field, $value);
    }

    /**************************************** Post Tags ***********************************************/
    public function setTagThisWeek(WP_Post $post, object $template): void
    {
        $today = Carbon::today();
        $start_of_week = $today->copy()->startOfWeek( CarbonInterface::MONDAY);
        $end_of_week = $today->copy()->endOfWeek( CarbonInterface::SUNDAY);

        //get the calendar events for the current week
        $data = $this->database->getCalenderByTemplateID( $template->id, $start_of_week, $end_of_week);

        if (count($data)) {
            $this->setPostTag($post, Constants::WP_TAG_THIS_WEEK);
        } else {
            $this->removePostTag($post, Constants::WP_TAG_THIS_WEEK);
        }
    }

    protected function setPostTag(WP_Post $post, string $tag): void
    {
        wp_set_object_terms( $post->ID, $tag, 'post_tag', true );
    }

    protected function removePostTag(WP_Post $post, string $tag)
    {
        //get list of tags for this post
        $currentTags = wp_get_post_terms( $post->ID );

        $updatedTags = [];

        foreach ( $currentTags as $currentTag) {
            $tag_string = http_build_query( $currentTag );

            if( ! str_contains( $tag_string, $tag ) ) {
                $updatedTags[] = $currentTag->name;
            }
        }

        wp_set_post_terms ($post->ID, $updatedTags);
    }

    /**************************************** Post Shortcodes ***********************************************/
    public function addEventDescriptionShortcodeToLinkedPost(WP_Post $post): void
    {
        $this->addShortcodeToPostContent($post, Constants::CUSTOM_FIELD_EVENT_DESC);
    }

    /**
     * Adds the given shortcode to the given post only if the shortcode is not already in the post's body
     * The new shortcode will be placed at the top right after the opening header if one exists, otherwise
     * the new shortcode will be placed at the very top of the content
     *
     * @param WP_Post $post
     * @param string $shortcode
     *
     * @return void
     */
    protected function addShortcodeToPostContent(WP_Post $post, string $shortcode): void
    {
        $content = $post->post_content;

        if ($this->shortcodeExists($content, $shortcode)) {
            //nothing to do, the shortcode already exists.
            return;
        }

        $block = $this->getShortcodeContentBlock($shortcode);

        $insertionPoint = $this->getInsertionPoint($content);

        if ($insertionPoint > 0) {
            $contentEdit = substr($content, 0, $insertionPoint);
            $contentEdit .= $block;
            $contentEdit .= substr($content, $insertionPoint, -1);
        } else {
            $contentEdit = $block;
            $contentEdit .= $content;
        }

        $post->post_content = $contentEdit;

        wp_update_post($post);
    }

    /**
     * Determine if the post's content includes the given shortcode
     * returns true if the shortcode is found anywhere in the post content body
     *
     * @param string $postContent
     * @param string $shortcode
     *
     * @return bool
     */
    protected function shortcodeExists(string $postContent, string $shortcode): bool
    {
        return str_contains($postContent, "[$shortcode]");
    }

    /**
     * Builds and returns the content block for the give shortcode
     *
     * @param string $shortcode
     *
     * @return string
     */
    protected function getShortcodeContentBlock(string $shortcode): string
    {
        return "\n\n<!-- wp:shortcode -->\n[$shortcode]\n<!-- /wp:shortcode -->\n";
    }

    /**
     * Determine where to insert the new shortcode content block
     *
     * If the post opens with a `wp:heading`, then insert right after that, otherwise insert at the top
     * It is expected that the contributor may want to move the block around the content body, so placing it at the
     * top ensure they see it.
     *
     * @param string $content
     *
     * @return int
     */
    protected function getInsertionPoint(string $content): int
    {
        //check if the post opens with a header
        if (str_starts_with($content, '<!-- wp:heading -->')) {
            return strpos($content, '-->', strpos($content, '<!-- /wp:heading -->')) + strlen('-->');
        }

        //if we didn't open with a header, then just insert at top of document
        return 0;
    }
}