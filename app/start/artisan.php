<?php

/*
|--------------------------------------------------------------------------
| Register The Artisan Commands
|--------------------------------------------------------------------------
|
| Each available Artisan command must be registered with the console so
| that it is available to be called. We'll register every command so
| the console gets access to each of the command object instances.
|
*/

Artisan::add(new chclcrawler);
Artisan::add(new elasticsearch);
Artisan::add(new elasticsearchdaily);
Artisan::add(new elasticsearchmapping);
Artisan::add(new sctjdeProducts);
Artisan::add(new dbclean);
Artisan::add(new workflow);
Artisan::add(new commission);
Artisan::add(new chclberth);
Artisan::add(new jdetablefix);
Artisan::add(new jdeporeconcialiation);
