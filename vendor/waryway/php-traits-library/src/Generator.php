<?php
namespace Waryway\PhpTraitsLibrary;

trait Generator
{
    /**
     * Read from a file one line at a time.
     *
     * @test
     * @param $fileName
     * @param callable|null $formatter - the default is a string.
     * @return \Generator|string|object
     */
    private function fileLineGenerator(string $fileName, callable $formatter = null)
    {
        $f = fopen($fileName, 'r');
        try {
            while ($line = fgets($f)) {
                if(!is_null($formatter)){
                    yield call_user_func($formatter, $line);
                }
                else {
                    yield $line;
                }
            }
        } finally {
            fclose($f);
        }
    }
}