<?php return [
  'Cache'  => [
	  'factory' => function() {
		  return new \Pipe\Cache\CacheController(new \Pipe\Cache\Storage\Filesystem(BASE_DIR . '/cache'));
	  }
  ],
  'Mail'  => [
    'class' => \Pipe\Mail\Mail::class,
    'binds' => \Pipe\Mail\IMailAware::class,
  ],
  'Test'  => [
    'class' => \Pipe\Test::class,
  ],
];