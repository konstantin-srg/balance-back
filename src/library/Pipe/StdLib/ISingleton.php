<?php
namespace Pipe\StdLib;

interface ISingleton
{
  static public function getInstance(): static;
}