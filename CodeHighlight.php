<?php if (!defined('PmWiki')) exit();
/**
  Programming languages syntax highlighting for PmWiki
  Written by (c) Petko Yotov 2023-2024   www.pmwiki.org/Petko
  
  This text is written for PmWiki; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 3
  of the License, or (at your option) any later version.
  See pmwiki.php for full details and lack of warranty.
  
  For Highlight.js, see lib/LICENSE

*/

$RecipeInfo['CodeHighlight']['Version'] = '2024-07-25';

SDVA($CodeHighlight, [
  'css-light' => '',
  'css-dark' => '',
  'languages' => [],
  'CDN' => 'https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@latest/build', 
  'scanCDN' => false,
]);

function initCodeHighlight() {
  global $CodeHighlight, $EnableHighlight;
  $EnableHighlight = 1;
  
  $conf = extGetConfig($CodeHighlight);
  
  $light = $conf['css-light'];
  $dark = $conf['css-dark'];
  $languages = $conf['languages'];
  
  $res = [];
  $attrs = [];
  
  if($light) {
    if(substr($light, -1) == '*') {
      $light = substr($light, 0, -1);
      $url = "{$CodeHighlight['CDN']}/styles/$light.min.css";
    }
    else {
      $url = "lib/styles/$light.min.css";
    }
    if($dark) {
      $attrs[$url] = [ 'data-theme' => 'light' ];
    }
    $res[$url] = 1;
  }
  else {
    $url = 'pmsyntax.highlight.css';
    $res[$url] = 1;
    if($dark) {
      $attrs[$url] = [ 'data-theme' => 'light' ];
    }
  }
  
  if($dark) {
    if(substr($dark, -1) == '*') {
      $dark = substr($dark, 0, -1);
      $url = "{$CodeHighlight['CDN']}/styles/$dark.min.css";
    }
    else {
      $url = "lib/styles/$dark.min.css";
    }
    
    $attrs[$url] = [ 'data-theme' => 'dark', 'disabled'];
    $res[$url] = 1;
  }
  
  $res['lib/highlight.min.js'] = 1;
  
  foreach($languages as $lang) {
    if(substr($lang, -1) == '*') {
      $lang = substr($lang, 0, -1);
      $url = "{$CodeHighlight['CDN']}/languages/$lang.min.js";
    }
    else {
      $url = "lib/languages/$lang.min.js";
    }
    $res[$url] = 1;
  }
  $resources = implode(' ', array_keys($res));
  
  extAddResource($resources, $attrs);
}

initCodeHighlight();


if($action == 'hub') {
  SDVA($MarkupDirectiveFunctions, ['highlightform'=>'FmtHighlightForm']);
}

function chScanResourceDir($dir) {
  $files = preg_grep('/^\\./', scandir($dir), PREG_GREP_INVERT);
  $files = preg_replace('/(\\.min)?\\.(css|js)$/', '', $files);
  sort($files);
  return $files;
}
function chScanCDN($dir, $exclude) {
  global $CodeHighlight;
  $res = [
    'styles' => 'a11y-dark a11y-light agate an-old-hope androidstudio arduino-light arta ascetic atom-one-dark atom-one-dark-reasonable atom-one-light brown-paper codepen-embed color-brewer dark default devibeans docco far felipec foundation github github-dark github-dark-dimmed gml googlecode gradient-dark gradient-light grayscale hybrid idea intellij-light ir-black isbl-editor-dark isbl-editor-light kimbie-dark kimbie-light lightfair lioshi magula mono-blue monokai monokai-sublime night-owl nnfx-dark nnfx-light nord obsidian panda-syntax-dark panda-syntax-light paraiso-dark paraiso-light pojoaque purebasic qtcreator-dark qtcreator-light rainbow routeros school-book shades-of-purple srcery stackoverflow-dark stackoverflow-light sunburst tokyo-night-dark tokyo-night-light tomorrow-night-blue tomorrow-night-bright vs vs2015 xcode xt256',
    'languages' => '1c abnf accesslog actionscript ada angelscript apache applescript arcade arduino armasm asciidoc aspectj autohotkey autoit avrasm awk axapta bash basic bnf brainfuck c cal capnproto ceylon clean clojure clojure-repl cmake coffeescript coq cos cpp crmsh crystal csharp csp css d dart delphi diff django dns dockerfile dos dsconfig dts dust ebnf elixir elm erb erlang erlang-repl excel fix flix fortran fsharp gams gauss gcode gherkin glsl gml go golo gradle graphql groovy haml handlebars haskell haxe hsp http hy inform7 ini irpf90 isbl java javascript jboss-cli json julia julia-repl kotlin lasso latex ldif leaf less lisp livecodeserver livescript llvm lsl lua makefile markdown mathematica matlab maxima mel mercury mipsasm mizar mojolicious monkey moonscript n1ql nestedtext nginx nim nix node-repl nsis objectivec ocaml openscad oxygene parser3 perl pf pgsql php php-template plaintext pony powershell processing profile prolog properties protobuf puppet purebasic python python-repl q qml r reasonml rib roboconf routeros rsl ruby ruleslanguage rust sas scala scheme scilab scss shell smali smalltalk sml sqf sql stan stata step21 stylus subunit swift taggerscript tap tcl thrift tp twig typescript vala vbnet vbscript vbscript-html verilog vhdl vim wasm wren x86asm xl xml xquery yaml zephir',
  ];
  if($CodeHighlight['scanCDN']) {
    $cdn = file_get_contents("{$CodeHighlight['CDN']}/$dir/");
    if(!$cdn) return [];
    preg_match_all("!build/$dir/([-\\w]+)\\.min\\.(css|js)!", $cdn, $m);
    $all = $m[1];
  }
  else {
    $all = explode(' ', $res[$dir]);
  }
  $out = array_diff($all, $exclude);
  sort($out);
  return $out;
}
  
function FmtHighlightForm($pagename, $d, $args) {
  global $CodeHighlight, $EnableHighlight;
  static $styles = [], $stylesCDN = [], $languages = [], $languagesCDN = [];
  
  $conf = array_merge($CodeHighlight, extGetConfig());
  
  $base = $conf['=dir'];
  
  $mode = $args['mode'];
  if(substr($mode, 0, 3) == 'css') {
    if(!$styles) {
      $styles = chScanResourceDir("$base/lib/styles");
      $stylesCDN = chScanCDN("styles", $styles);
    }
    
    if($mode == 'css-light')
      $out = "(:input select $mode '' 'PmSyntax ($[light+dark])':)";
    else 
      $out = "(:input select $mode '' '$[None]':)";
    
    foreach($styles as $name) {
      $out .= "(:input select $mode $name:)";
    }
    foreach($stylesCDN as $name) {
      $out .= "(:input select $mode $name* '$name (CDN)':)";
    }
  
  }
  elseif($mode == 'languages') {
    $commonlangs = array_flip(explode(' ', "bash c cpp csharp css diff go ini java javascript json less lua makefile markdown objectivec perl php php-template plaintext python python-repl r ruby rust scss shell swift typescript vbnet wasm xml yaml"));
    
    if(!$languages) {
      $languages = chScanResourceDir("$base/lib/languages");
      $exclude = array_merge($languages, array_keys($commonlangs));
      
      $languagesCDN = chScanCDN("languages", $exclude);
    }
    
    $out = "* '''$[Common languages (included):]''' "
      . implode(', ', array_keys($commonlangs)) 
        . " %item column-span=all padding='.5em 0'%" 
        ." %list filterable columns=8.5em padding=0 list-style=none%\n";
    
    foreach($languages as $lang) {
      $out .= "* (:input checkbox languages[] $lang $lang title=\"Local\":)\n";
    }
    
    $out .= "* '''$[These can be loaded from CDN:]''' %item column-span=all padding-top=.5em%\n";
    
    foreach($languagesCDN as $lang) {
      $out .= "* (:input checkbox languages[] $lang* '$lang*' title=\"CDN\":)\n";
    }
  }
  return $out;
}

