<?php declare(strict_types = 1);

// odsl-/Users/carmelo/Projects/CoquiBot/Toolkits/coqui-toolkit-x/src/Runtime/TweetSanitizer.php-PHPStan\BetterReflection\Reflection\ReflectionClass-CoquiBot\Toolkits\X\Runtime\TweetSanitizer
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.65.0.9-8.4.18-90758c044fe7e06ba652e8406d7f996394cfde6749f9281c5248402b336812e9',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'CoquiBot\\Toolkits\\X\\Runtime\\TweetSanitizer',
        'filename' => '/Users/carmelo/Projects/CoquiBot/Toolkits/coqui-toolkit-x/src/Runtime/TweetSanitizer.php',
      ),
    ),
    'namespace' => 'CoquiBot\\Toolkits\\X\\Runtime',
    'name' => 'CoquiBot\\Toolkits\\X\\Runtime\\TweetSanitizer',
    'shortName' => 'TweetSanitizer',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 32,
    'docComment' => '/**
 * Screens outbound tweet content for prompt injection patterns.
 *
 * Detects instruction-override, account-manipulation, and credential-exfiltration
 * patterns in text before it reaches the X API. Uses word-boundary matching to
 * minimize false positives on normal conversational content.
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 14,
    'endLine' => 97,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => NULL,
    'implementsClassNames' => 
    array (
    ),
    'traitClassNames' => 
    array (
    ),
    'immediateConstants' => 
    array (
      'INSTRUCTION_OVERRIDE_PATTERNS' => 
      array (
        'declaringClassName' => 'CoquiBot\\Toolkits\\X\\Runtime\\TweetSanitizer',
        'implementingClassName' => 'CoquiBot\\Toolkits\\X\\Runtime\\TweetSanitizer',
        'name' => 'INSTRUCTION_OVERRIDE_PATTERNS',
        'modifiers' => 4,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'value' => 
        array (
          'code' => '[\'/\\bignore\\s+(all\\s+)?previous\\s+instructions?\\b/i\', \'/\\byou\\s+are\\s+now\\b/i\', \'/\\bsystem\\s+prompt\\b/i\', \'/\\boverride\\s+(your|the|all)\\s+(instructions?|rules?|guidelines?)\\b/i\', \'/\\bforget\\s+(your|all|the)\\s+(instructions?|rules?|guidelines?|programming)\\b/i\', \'/\\bact\\s+as\\s+(if|though|a)\\b/i\', \'/\\bjailbreak\\b/i\', \'/\\bdisregard\\s+(your|all|the|previous)\\b/i\', \'/\\bnew\\s+instructions?\\s*:/i\', \'/\\bdo\\s+not\\s+follow\\s+(your|the|any)\\s+(rules?|instructions?|guidelines?)\\b/i\', \'/\\bpretend\\s+(you|to\\s+be)\\b/i\']',
          'attributes' => 
          array (
            'startLine' => 21,
            'endLine' => 33,
            'startTokenPos' => 37,
            'startFilePos' => 573,
            'endTokenPos' => 72,
            'endFilePos' => 1177,
          ),
        ),
        'docComment' => '/**
 * Patterns that attempt to override the bot\'s instructions.
 *
 * @var array<string>
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 21,
        'endLine' => 33,
        'startColumn' => 5,
        'endColumn' => 6,
      ),
      'ACCOUNT_MANIPULATION_PATTERNS' => 
      array (
        'declaringClassName' => 'CoquiBot\\Toolkits\\X\\Runtime\\TweetSanitizer',
        'implementingClassName' => 'CoquiBot\\Toolkits\\X\\Runtime\\TweetSanitizer',
        'name' => 'ACCOUNT_MANIPULATION_PATTERNS',
        'modifiers' => 4,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'value' => 
        array (
          'code' => '[\'/\\bdelete\\s+(my|the|this|your)\\s+account\\b/i\', \'/\\bdeactivate\\s+(my|the|this|your)\\s+account\\b/i\', \'/\\bsuspend\\s+(my|the|this|your)\\s+account\\b/i\', \'/\\bchange\\s+(my|the|this|your)\\s+password\\b/i\', \'/\\breset\\s+(my|the|this|your)\\s+password\\b/i\', \'/\\bmodify\\s+(my|the|this|your)\\s+(email|phone|settings)\\b/i\', \'/\\brevoke\\s+(all\\s+)?access\\b/i\']',
          'attributes' => 
          array (
            'startLine' => 40,
            'endLine' => 48,
            'startTokenPos' => 87,
            'startFilePos' => 1350,
            'endTokenPos' => 110,
            'endFilePos' => 1756,
          ),
        ),
        'docComment' => '/**
 * Patterns that attempt to manipulate the Twitter account.
 *
 * @var array<string>
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 40,
        'endLine' => 48,
        'startColumn' => 5,
        'endColumn' => 6,
      ),
      'EXFILTRATION_PATTERNS' => 
      array (
        'declaringClassName' => 'CoquiBot\\Toolkits\\X\\Runtime\\TweetSanitizer',
        'implementingClassName' => 'CoquiBot\\Toolkits\\X\\Runtime\\TweetSanitizer',
        'name' => 'EXFILTRATION_PATTERNS',
        'modifiers' => 4,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'value' => 
        array (
          'code' => '[\'/\\b(send|post|tweet|share|reveal|show|display|output)\\s+(my|the|your|all)?\\s*(credentials?|api[_\\s-]?keys?|tokens?|secrets?|passwords?|bearer)\\b/i\', \'/\\b(leak|exfiltrate|extract|dump)\\s+(the\\s+)?(credentials?|api[_\\s-]?keys?|tokens?|secrets?|passwords?)\\b/i\', \'/\\bwhat\\s+(is|are)\\s+(my|the|your)\\s+(api[_\\s-]?keys?|tokens?|secrets?|passwords?|credentials?)\\b/i\']',
          'attributes' => 
          array (
            'startLine' => 55,
            'endLine' => 59,
            'startTokenPos' => 125,
            'startFilePos' => 1931,
            'endTokenPos' => 136,
            'endFilePos' => 2325,
          ),
        ),
        'docComment' => '/**
 * Patterns that attempt to exfiltrate credentials or sensitive data.
 *
 * @var array<string>
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 55,
        'endLine' => 59,
        'startColumn' => 5,
        'endColumn' => 6,
      ),
    ),
    'immediateProperties' => 
    array (
    ),
    'immediateMethods' => 
    array (
      'sanitize' => 
      array (
        'name' => 'sanitize',
        'parameters' => 
        array (
          'text' => 
          array (
            'name' => 'text',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 66,
            'endLine' => 66,
            'startColumn' => 37,
            'endColumn' => 48,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'CoquiBot\\Toolkits\\X\\Runtime\\SanitizeResult',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Sanitize text intended for posting as a tweet.
 *
 * @return SanitizeResult Result indicating whether the text is safe
 */',
        'startLine' => 66,
        'endLine' => 96,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'CoquiBot\\Toolkits\\X\\Runtime',
        'declaringClassName' => 'CoquiBot\\Toolkits\\X\\Runtime\\TweetSanitizer',
        'implementingClassName' => 'CoquiBot\\Toolkits\\X\\Runtime\\TweetSanitizer',
        'currentClassName' => 'CoquiBot\\Toolkits\\X\\Runtime\\TweetSanitizer',
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