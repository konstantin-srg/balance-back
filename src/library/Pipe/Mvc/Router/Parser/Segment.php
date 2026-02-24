<?php
namespace Pipe\Mvc\Router\Parser;

class Segment implements IParser
{
  public function match($route, string $url, $full = true): array | bool
  {
    $regex = $this->regexp($this->parseRouteDefinition($route['route']), $route['constraints']);
    $matches = [];


    preg_match('(^' . $regex . ($full ? '$' : '') . ')', $url, $matches);

    if(!$matches) return false;

    $url = substr($url, strlen($matches[0]));

    $vars = [];
    foreach ($this->paramMap as $key => $fieldName) {
      $vars[$key] = $matches[$fieldName] ?? '';
    }

    return [
      'vars'  => $vars,
      'url'   => $url,
    ];
  }

  protected $paramMap = [];

  protected function regexp(array $parts, array $constraints, &$groupIndex = 1)
  {
    $regex = '';

    foreach ($parts as $part) {
      switch ($part[0]) {
        case 'literal':
          $regex .= preg_quote($part[1]);
          break;
        case 'parameter':
          $groupName = '?P<param' . $groupIndex . '>';
          if (isset($constraints[$part[1]])) {
            $regex .= '(' . $groupName . $constraints[$part[1]] . ')';
          } elseif ($part[2] === null) {
            $regex .= '(' . $groupName . '[^/]+)';
          } else {
            $regex .= '(' . $groupName . '[^' . $part[2] . ']+)';
          }
          //$this->paramMap['param' . $groupIndex++] = $part[1];
          $this->paramMap[$part[1]] = 'param' . $groupIndex++;
          break;

        case 'optional':
          $regex .= '(?:' . $this->regexp($part[1], $constraints, $groupIndex) . ')?';
          break;
      }
    }
    return $regex;
  }


  protected function parseRouteDefinition($def)
  {
    $currentPos = 0;
    $length     = strlen($def);
    $parts      = [];
    $levelParts = [&$parts];
    $level      = 0;

    while ($currentPos < $length) {
      preg_match('(\G(?P<literal>[^:{\[\]]*)(?P<token>[:{\[\]]|$))', $def, $matches, 0, $currentPos);

      $currentPos += strlen($matches[0]);

      if (! empty($matches['literal'])) {
        $levelParts[$level][] = ['literal', $matches['literal']];
      }

      if ($matches['token'] === ':') {
        if (!preg_match('(\G(?P<name>[^:/{\[\]]+)(?:{(?P<delimiters>[^}]+)})?:?)', $def, $matches,0,$currentPos)) {
          throw new \Exception('Found empty parameter name');
        }

        $levelParts[$level][] = [
          'parameter',
          $matches['name'],
          $matches['delimiters'] ?? null,
        ];

        $currentPos += strlen($matches[0]);
      } elseif ($matches['token'] === '{') {
        if (! preg_match('(\G(?P<literal>[^}]+)\})', $def, $matches, 0, $currentPos)) {
          throw new \Exception('Translated literal missing closing bracket');
        }

        $currentPos += strlen($matches[0]);

        $levelParts[$level][] = ['translated-literal', $matches['literal']];
      } elseif ($matches['token'] === '[') {
        $levelParts[$level][]   = ['optional', []];
        $levelParts[$level + 1] = &$levelParts[$level][count($levelParts[$level]) - 1][1];

        $level++;
      } elseif ($matches['token'] === ']') {
        unset($levelParts[$level]);
        $level--;

        if ($level < 0) {
          throw new \Exception('Found closing bracket without matching opening bracket');
        }
      } else {
        break;
      }
    }

    if ($level > 0) {
      throw new \Exception('Found unbalanced brackets');
    }

    return $parts;
  }
}