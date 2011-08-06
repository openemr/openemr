<?php
class ProblemList extends AbstractAmcReport
{
    public function getTitle()
    {
        return "Problem List";
    }
    
    public function createDenominator() 
    {
        return new ProblemList_Denominator();
    }
    
    public function createNumerator()
    {
        return new ProblemList_Numerator();
    }
}