<?php
namespace Pipe\DModel\DBAdapter;

use PDO;

trait TPdo
{
  private PDO $pdo;

	public function setPdo(PDO $pdo): static
	{
		$this->pdo = $pdo;
		return $this;
	}

  public function pdo(): PDO
  {
    return $this->pdo;
  }
}