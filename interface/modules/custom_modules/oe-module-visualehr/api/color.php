<?php

function getColor()
{
    $colors = ["#eb5ea8", "#9f4ed3", "#99b1fc", "#7e83d0", "#fb4e4e", "#32a852", "#fb4e4e"];
    $randColorIndex = random_int(0, count($colors) - 1);
    $generatedColor = $colors[$randColorIndex];
    return $generatedColor;
}
