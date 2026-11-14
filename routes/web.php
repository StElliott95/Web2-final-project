<?php
require __DIR__.'/discovery.php';
require __DIR__.'/reviews.php';
require __DIR__.'/watchlist.php';
require __DIR__.'/session.php';
// Task modules can be integrated independently.
foreach (['recommendations','lists','admin'] as $feature) {
    if (is_file(__DIR__.'/'.$feature.'.php')) require __DIR__.'/'.$feature.'.php';
}
require __DIR__.'/integrations.php';
