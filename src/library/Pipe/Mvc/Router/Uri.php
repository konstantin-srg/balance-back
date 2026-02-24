<?php
namespace Pipe\Mvc\Router;

class Uri
{
  protected string $host;
  protected array  $domain = [];
  protected array  $params = [];
  protected string $path;

  protected array $uri = [
    'domain'  => [],
    'path'    => '',
    'host'    => '',
  ];

  public function __construct()
  {
    $domain = explode('.', $_SERVER['HTTP_HOST']);
    $cnt = count($domain);

    $cnt < 3 ?: $this->domain['tld'] = array_pop($domain);
    $this->domain['domain'] = array_pop($domain);
    $this->domain['sub']  = array_pop($domain);

    $uri   = $_SERVER['REQUEST_URI'];

    if (preg_match('|^[^\?#]*|', $uri, $match)) {
      $this->path = $match[0];
      $uri = substr($uri, strlen($match[0]));
    }

    if (preg_match('|^\?([^#]*)|', $uri, $match)) {
      $this->params = $this->uri['query'];
    }
  }

  public function host(): string
  {
    return $this->host;
  }

  public function domain(): array
  {
    return $this->domain;
  }

  public function path(): string
  {
    return $this->path;
  }

  public function params(): array
  {
    return $this->params;
  }
}