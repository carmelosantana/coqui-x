<?php declare(strict_types = 1);

// odsl-/Users/carmelo/Projects/CoquiBot/Toolkits/coqui-toolkit-x/src/XToolkit.php-PHPStan\BetterReflection\Reflection\ReflectionClass-CoquiBot\Toolkits\X\XToolkit
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.65.0.9-8.4.18-5a5855d3bc307428617cef18495dc3ca72aa5d7eaa3a1ede7623800150a6d7b9',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'CoquiBot\\Toolkits\\X\\XToolkit',
        'filename' => '/Users/carmelo/Projects/CoquiBot/Toolkits/coqui-toolkit-x/src/XToolkit.php',
      ),
    ),
    'namespace' => 'CoquiBot\\Toolkits\\X',
    'name' => 'CoquiBot\\Toolkits\\X\\XToolkit',
    'shortName' => 'XToolkit',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 32,
    'docComment' => '/**
 * X (Twitter) management toolkit for Coqui Bot.
 *
 * Provides 8 tools for comprehensive X API v2 interaction:
 * tweet management, timelines, search, user lookup, followers,
 * likes, bookmarks, and mutes.
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 25,
    'endLine' => 131,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => NULL,
    'implementsClassNames' => 
    array (
      0 => 'CarmeloSantana\\PHPAgents\\Contract\\ToolkitInterface',
    ),
    'traitClassNames' => 
    array (
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
      'client' => 
      array (
        'declaringClassName' => 'CoquiBot\\Toolkits\\X\\XToolkit',
        'implementingClassName' => 'CoquiBot\\Toolkits\\X\\XToolkit',
        'name' => 'client',
        'modifiers' => 132,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'CoquiBot\\Toolkits\\X\\Runtime\\XClient',
            'isIdentifier' => false,
          ),
        ),
        'default' => NULL,
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 27,
        'endLine' => 27,
        'startColumn' => 5,
        'endColumn' => 37,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
    ),
    'immediateMethods' => 
    array (
      '__construct' => 
      array (
        'name' => '__construct',
        'parameters' => 
        array (
          'client' => 
          array (
            'name' => 'client',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 29,
                'endLine' => 29,
                'startTokenPos' => 101,
                'startFilePos' => 847,
                'endTokenPos' => 101,
                'endFilePos' => 850,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'CoquiBot\\Toolkits\\X\\Runtime\\XClient',
                      'isIdentifier' => false,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 29,
            'endLine' => 29,
            'startColumn' => 33,
            'endColumn' => 55,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 29,
        'endLine' => 32,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'CoquiBot\\Toolkits\\X',
        'declaringClassName' => 'CoquiBot\\Toolkits\\X\\XToolkit',
        'implementingClassName' => 'CoquiBot\\Toolkits\\X\\XToolkit',
        'currentClassName' => 'CoquiBot\\Toolkits\\X\\XToolkit',
        'aliasName' => NULL,
      ),
      'tools' => 
      array (
        'name' => 'tools',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 34,
        'endLine' => 46,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'CoquiBot\\Toolkits\\X',
        'declaringClassName' => 'CoquiBot\\Toolkits\\X\\XToolkit',
        'implementingClassName' => 'CoquiBot\\Toolkits\\X\\XToolkit',
        'currentClassName' => 'CoquiBot\\Toolkits\\X\\XToolkit',
        'aliasName' => NULL,
      ),
      'guidelines' => 
      array (
        'name' => 'guidelines',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 48,
        'endLine' => 130,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'CoquiBot\\Toolkits\\X',
        'declaringClassName' => 'CoquiBot\\Toolkits\\X\\XToolkit',
        'implementingClassName' => 'CoquiBot\\Toolkits\\X\\XToolkit',
        'currentClassName' => 'CoquiBot\\Toolkits\\X\\XToolkit',
        'aliasName' => NULL,
      ),
    ),
    'traitsData' => 
    array (
      'aliases' => 
      array (
      ),
      'modifiers' => 
      array (
      ),
      'precedences' => 
      array (
      ),
      'hashes' => 
      array (
      ),
    ),
  ),
));