<?php
namespace Pipe\StdLib;

trait TConfigurable
{
  protected array $config= [];
  public function __construct(array $config = [])
  {
    $this->config = $config;
  }
	public function getConfig(): array
	{
		return $this->config;
	}
}