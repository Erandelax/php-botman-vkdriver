<?php

/**
 * Git: https://github.com/TzepART/botman-vk-driver
 * Class Source: https://raw.githubusercontent.com/TzepART/botman-vk-driver/master/src/Drivers/VkAttachmentDriver.php
 */

namespace BotMan\Drivers\VK;


use BotMan\BotMan\Messages\Attachments\File;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;

/**
 * Class VkAttachmentDriver
 * @package VkBotMan\Drivers
 */
class VkAttachmentDriver extends VkDriver
{
    /**
     * @return bool
     */
    public function matchesRequest()
    {
        $attachments = $this->event->get('attachments');

        return is_array($attachments) && count($attachments) !== 0;
    }

    /**
     * @return bool
     */
    public function hasMatchingEvent()
    {
        return false;
    }

    /**
     * Retrieve the chat message.
     *
     * @return array
     */
    public function getMessages()
    {
        if (empty($this->messages)) {
            $this->loadMessages();
        }

        return $this->messages;
    }

    /**
     *
     */
    public function loadMessages()
    {
        $message = null;

        if (count($this->getImages()) > 0) {
            $message = new IncomingMessage(Image::PATTERN,
                $this->event->get('from_id'),
                $this->payload->get('group_id'),
                $this->event
            );
            $message->setImages($this->getImages());
        }
        elseif (count($this->getAttachments()) > 0) {
            $message = new IncomingMessage(File::PATTERN,
                $this->event->get('from_id'),
                $this->payload->get('group_id'),
                $this->event
            );
            $message->setFiles($this->getAttachments());
        }

        if (null !== $message) {
            $this->messages = [$message];
        }
    }

    /**
     * @return array
     */
    private function getAttachments()
    {
        $attachments = $this->event->get('attachments');
        $output = [];

        foreach ($attachments as $attachment) {
            if (isset($attachment['photo'])) {
                continue;
            }

            $file = new File('http://', $attachment);
            $output[] = $file;
        }

        return $output;
    }

    /**
     * Retrieve a image from an incoming message.
     * @return array A download for the image file.
     */
    private function getImages()
    {
        $photos = $this->event->get('attachments');
        $images = [];

        foreach ($photos as $photo) {

            if (!isset($photo['photo'])) {
                continue;
            }

            $firstPhoto = $photo;
            $sizes = $firstPhoto['photo']['sizes'];

            $biggest = null;
            $sizeb = 0;

            foreach ($sizes as $size) {
                if ($size['height'] > $sizeb) {
                    $biggest = $size;
                    $sizeb = $size['height'];
                }
            }

            $image = new Image($biggest['url'], $biggest);
            $images[] = $image;
        }

        return $images;
    }

    /**
     * @return bool
     */
    public function isConfigured()
    {
        return false;
    }
}