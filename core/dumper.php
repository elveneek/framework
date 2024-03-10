<?php
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
//use SymfonyRoadrunner\Exception\Dump;

function ddd(...$vars)
{
    $dumper = new HtmlDumper;
    $throwable = new Exception();
    $strs =[];
    foreach ($vars as $var) {
        $strs[] =      $dumper->dump((new VarCloner)->cloneVar($var), true);
    }

    print  implode(\PHP_EOL, $strs);
    return;
    throw new Exception( implode(\PHP_EOL, $strs));
    throw $throwable;
}