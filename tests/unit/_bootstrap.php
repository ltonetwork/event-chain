<?php

use Jasny\Container\Container;

$entries = new AppendIterator();
$entries->append(App::getContainerEntries());

$container = new Container($entries);
App::setContainer($container);
