<?php

interface IAmcItemizedReport
{
    /**
     * Returns our action data that we wish to store in the database
     * @return AmcItemizedActionData
     */
    public function getItemizedDataForLastTest(): AmcItemizedActionData;

    /**
     * Returns the hydrated (language translated) data record that came from the itemized data record
     * @return AmcItemizedActionData
     */
    public function hydrateItemizedDataFromRecord($actionData): AmcItemizedActionData;
}
