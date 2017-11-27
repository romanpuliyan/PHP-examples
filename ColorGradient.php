<?php

namespace frontend\models\service\color;

class ColorGradient
{

    protected $red;
    protected $orange;
    protected $green;

    protected $start;
    protected $end;

    protected $value;

    const DEFAULT_COLOR = 'rgb(232, 9, 26)';

    public function __construct()
    {
        $this->red    = new ColorScheme(232, 9, 26);
        $this->orange = new ColorScheme(255, 175, 75);
        $this->green  = new ColorScheme(6, 170, 60);
    }

    public function setValue($value)
    {
        $this->value = (int) $value;
        return $this;
    }

    public function getColor()
    {
        $this->prepare();

        $startColors = $this->start;
        $endColors   = $this->end;

        $r = $this->interpolate($startColors->r, $endColors->r, 50, $this->value);
        $g = $this->interpolate($startColors->g, $endColors->g, 50, $this->value);
        $b = $this->interpolate($startColors->b, $endColors->b, 50, $this->value);

        return "rgb($r, $g, $b)";
    }

    protected function prepare()
    {

        $this->start = $this->red;
        $this->end   = $this->orange;

        if($this->value > 50) {
            $this->start = $this->orange;
            $this->end   = $this->green;
            $this->value = $this->value % 51;
        }
    }

    protected function interpolate($start, $end, $steps, $count)
    {
        $final = $start + ((($end - $start) / $steps) * $count);
        return floor($final);
    }
}
