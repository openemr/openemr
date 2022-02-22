<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\ui;

class FormTestElement
{
    public function focus($session, $id)
    {
        $crawler = $session[0];
        $client = $session[1];
        $form = "//form[@id='$id']";
        $client->waitForVisibility($form);

        return $crawler->filterXPath($form)->form();
    }

    public function fill($session, $name, $data)
    {
        $form = (new FormTestElement)->focus($session, $name);

        foreach ($data as $key => $value)
        {
            $form[$key] = $value;
        }
    }

    public function findValidationError($session, $error)
    {
        $client = $session[1];
        $message = "//span[contains(text(),'$error')]";
        $client->waitForVisibility($message);
    }

    public function isDisplayed($session, $id)
    {
        $crawler = $session[0];

        try {
            $crawler->filterXPath("//form[@id='$id']")->isDisplayed();
        } catch (\Exception $e) {
            return false;
        }
    }
}