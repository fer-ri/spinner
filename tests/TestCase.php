<?php

use Ferri\Spinner;

class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function test_spin_flat_text()
    {
        $string = '{The|A} {quick|speedy|fast} {brown|black|red} {fox|wolf} {jumped|bounded|hopped|skipped} over the {lazy|tired} {dog|hound}';

        $result = Spinner::flat($string);

        $this->assertFalse($this->strContains($result, ['{', '}', '|']));
    }

    /**
     * @test
     */
    public function test_spin_nested_text()
    {
        $string = '{{The|A} {quick|speedy|fast} {brown|black|red}} {{fox|wolf} {jumped|bounded|hopped|skipped} over the {lazy|tired} {dog|hound}}';

        $result = Spinner::nested($string);

        $this->assertFalse($this->strContains($result, ['{', '}', '|']));
    }

    /**
     * @test
     */
    public function test_spin_auto_detect_text_with_seed()
    {
        $string = '{{The|A} {quick|speedy|fast} {brown|black|red}} {{fox|wolf} {jumped|bounded|hopped|skipped} over the {lazy|tired} {dog|hound}}';

        $a = Spinner::detect($string, true);

        $b = Spinner::detect($string, true);

        $this->assertFalse($this->strContains($a, ['{', '}', '|']));
        $this->assertFalse($this->strContains($b, ['{', '}', '|']));
        $this->assertSame($a, $b);
    }

    private function strContains($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle != '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}
