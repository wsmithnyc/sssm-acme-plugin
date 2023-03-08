<?php

namespace SeaportAcmeTicketing;

use Carbon\Carbon;
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
        //set the active event calendar dates
        $this->updatePostEventDates($post, $template);
        //set the event description
        $this->updatePostDescription($post, $template);
        //set the event short description
        $this->updatePostShortDescriptiong($post, $template);
        //set the event title
        $this->updatePostTitle($post, $template);
        //set the Book Now URL
        $this->updatePostBookUrl($post, $template);
    }

    public function updatePostTitle(WP_Post $post, object $template): void
    {
        $this->setCustomField($post, Constants::CUSTOM_FIELD_EVENT_TITLE, $template->name);
    }

    public function updatePostShortDescriptiong(WP_Post $post, object $template): void
    {
        $this->setCustomField($post, Constants::CUSTOM_FIELD_EVENT_SHORT_DESC, $template->short_description);
    }

    public function updatePostDescription(WP_Post $post, object $template): void
    {
        $this->setCustomField($post, Constants::CUSTOM_FIELD_EVENT_DESC, $template->description);
    }

    public function updatePostEventDates(WP_Post $post, object $template): void
    {
        $data = $this->database->getCalenderbyTemplateID($template->id, Carbon::today(), Carbon::today()->addMonth());
        $dates = [];

        foreach ($data as $row) {
            $dates[] = $row->event_date;
        }

        $value = implode("\n", $dates);
        $this->setCustomField($post, Constants::CUSTOM_FIELD_EVENT_DATES, $value);
    }

    public function updatePostBookUrl(WP_Post $post, object $template): void
    {
        $this->setCustomField($post, Constants::CUSTOM_FIELD_BOOK_NOW, Helpers::getBookingUrl($template->id));
    }

    public function setCustomField(WP_Post $post, string $field, string $value): void
    {
        update_post_meta($post->ID, $field, $value);
    }
}