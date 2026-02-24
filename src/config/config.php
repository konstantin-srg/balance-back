<?php return [
  'Cache'  => [
	  'factory' => function() {
		  return new \Pipe\Cache\CacheController(new \Pipe\Cache\Storage\Filesystem(BASE_DIR . '/cache'));
	  }
  ],
  'Ozon'  => [
	  'factory' => function() {
		  return new Pipe\Ozon\Ozon([
			  'login'    => '928',
			  'password' => '9ad5d33e-10e2-465d-a732-84d9c6fc7183',
			  'server'   => 'https://api-seller.ozon.ru/',
		  ]);
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