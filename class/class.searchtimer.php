<?php
//=================================================
// timer for profiling

class SearchTimer
{
    public $time = 0;

    public $marks = [];

    public function __construct()
    {
        $this->time = $this->getTime();
    }

    public function start($name)
    {
        if (!isset($this->marks[$name])) {
            $this->marks[$name] = $this->getTime();
        }
    }

    public function stop($name)
    {
        if (isset($this->marks[$name])) {
            $this->marks[$name] = $this->getTime() - $this->marks[$name];
        } else {
            $this->marks[$name] = 0;
        }
    }

    public function get($name)
    {
        return $this->marks[$name];
    }

    public function display()
    {
        echo '<table border="1">';

        foreach ($this->marks as $name => $value) {
            echo "<tr><td>$name</td><td>$value</td></tr>";
        }

        echo '</table> Total time' . $this->getTime();
    }

    // increase precision with deltime

    public function getTime()
    {
        return array_sum(explode(' ', microtime())) - $this->time;
    }
}
