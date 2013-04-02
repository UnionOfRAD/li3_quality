<?php

namespace li3_quality\tests\cases\test\rules;

class ControlStructuresHaveCorrectSpacingTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\test\rules\ControlStructuresHaveCorrectSpacing';

	public function testCorrectIf() {
		$code = <<<EOD
if (true) {
	return false;
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIncorrectIfNoFirstLineSpacing() {
		$code = <<<EOD
if(true) {
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIncorrectIfWithBracketOnNewline() {
		$code = <<<EOD
if (true)
{
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIncorrectIfWithNoEndingSpace() {
		$code = <<<EOD
if (true){
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIncorrectIfWithNoBeginningSpace() {
		$code = <<<EOD
if(true) {
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectIfWithFirstExpressionSpace() {
		$code = <<<EOD
if ( true) {
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectIfWithLastExpressionSpace() {
		$code = <<<EOD
if (true ) {
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectIfWithFullExpressionSpace() {
		$code = <<<EOD
if ( true ) {
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectWhile() {
		$code = <<<EOD
while (true) {
	return false;
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIncorrectWhileNoFirstLineSpacing() {
		$code = <<<EOD
while(true){
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIncorrectWhileWithBracketOnNewline() {
		$code = <<<EOD
while (true)
{
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIncorrectWhileWithNoEndingSpace() {
		$code = <<<EOD
while (true){
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIncorrectWhileWithNoBeginningSpace() {
		$code = <<<EOD
while(true) {
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectWhileWithFirstExpressionSpace() {
		$code = <<<EOD
while ( true) {
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectWhileWithLastExpressionSpace() {
		$code = <<<EOD
while (true ) {
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectWhileWithFullExpressionSpace() {
		$code = <<<EOD
while ( true ) {
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectFor() {
		$code = <<<EOD
for (true) {
	return false;
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIncorrectForNoFirstLineSpacing() {
		$code = <<<EOD
for(true){
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIncorrectForWithBracketOnNewline() {
		$code = <<<EOD
for (true)
{
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIncorrectForWithNoEndingSpace() {
		$code = <<<EOD
for (true){
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIncorrectForWithNoBeginningSpace() {
		$code = <<<EOD
for(true) {
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectForWithFirstExpressionSpace() {
		$code = <<<EOD
for ( true) {
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectForWithLastExpressionSpace() {
		$code = <<<EOD
for (true ) {
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectForWithFullExpressionSpace() {
		$code = <<<EOD
for ( true ) {
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectForeach() {
		$code = <<<EOD
foreach (true) {
	return false;
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIncorrectForeachNoFirstLineSpacing() {
		$code = <<<EOD
foreach(true){
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIncorrectForeachWithBracketOnNewline() {
		$code = <<<EOD
foreach (true)
{
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIncorrectForeachWithNoEndingSpace() {
		$code = <<<EOD
foreach (true){
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIncorrectForeachWithNoBeginningSpace() {
		$code = <<<EOD
foreach(true) {
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectForeachWithFirstExpressionSpace() {
		$code = <<<EOD
foreach ( true) {
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectForeachWithLastExpressionSpace() {
		$code = <<<EOD
foreach (true ) {
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectForeachWithFullExpressionSpace() {
		$code = <<<EOD
foreach ( true ) {
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectElse() {
		$code = <<<EOD
if (true) {
	return false;
} else {
	return true;
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIncorrectElseNoFirstLineSpacing() {
		$code = <<<EOD
if (true) {
	return false;
}else{
	return true;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIncorrectElseWithBracketOnNewline() {
		$code = <<<EOD
if (true) {
	return false;
} else
{
	return true;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIncorrectElseWithNoEndingSpace() {
		$code = <<<EOD
if (true) {
	return false;
} else{
	return true;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIncorrectElseWithNoBeginningSpace() {
		$code = <<<EOD
if (true) {
	return false;
}else {
	return true;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectElseIfWithNoSpace() {
		$code = <<<EOD
if (true) {
	return false;
} elseif (true) {
	return true;
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIncorrectElseIfWithSpace() {
		$code = <<<EOD
if (true) {
	return false;
} else if (true) {
	return true;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIncorrectElseIfWithNoSpaceNoFirstLineSpacing() {
		$code = <<<EOD
if (true) {
	return false;
}elseif(true){
	return true;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIncorrectElseIfWithNoSpaceWithBracketOnNewline() {
		$code = <<<EOD
if (true) {
	return false;
} elseif (true)
{
	return true;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIncorrectElseIfWithNoSpaceWithNoEndingSpace() {
		$code = <<<EOD
if (true) {
	return false;
} elseif (true){
	return true;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIncorrectElseIfWithNoSpaceWithNoBeginningSpace() {
		$code = <<<EOD
if (true) {
	return false;
}elseif (true) {
	return true;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectElseIfWithFirstExpressionSpace() {
		$code = <<<EOD
if (true) {
	return false;
} elseif ( true) {
	return true;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectElseIfWithLastExpressionSpace() {
		$code = <<<EOD
if (true) {
	return false;
} elseif (true ) {
	return true;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectElseIfWithFullExpressionSpace() {
		$code = <<<EOD
if (true) {
	return false;
} elseif ( true ) {
	return true;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectDoWhile() {
		$code = <<<EOD
do {
	return true;
} while (true);
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIncorrectDoWhileWithNoSpacesOnFirstLine() {
		$code = <<<EOD
do{
	return true;
} while (true);
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIncorrectDoWhileWithNoSpacesOnLastLine() {
		$code = <<<EOD
do {
	return true;
}while(true);
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIncorrectDoWhileWithNoFirstSpaceOnWhile() {
		$code = <<<EOD
do {
	return true;
}while (true);
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testIncorrectDoWhileWithNoLastSpaceOnWhile() {
		$code = <<<EOD
do {
	return true;
} while(true);
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectDoWhileWithFirstExpressionSpace() {
		$code = <<<EOD
do {
	return true;
} while( true);
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectDoWhileWithLastExpressionSpace() {
		$code = <<<EOD
do {
	return true;
} while(true );
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectDoWhileWithFullExpressionSpace() {
		$code = <<<EOD
do {
	return true;
} while( true );
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testAcceptableMultilineExpression() {
		$code = <<<EOD
if (true && (
	false || null
)) {
	return false;
}

if (
	true &&
	false ||
	null
) {
	return false;
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIncorrectMultilineExpression() {
		$code = <<<EOD
if (true && (
	false || null))
{
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);

		$code = <<<EOD
if (
	true &&
	false ||
	null
)
{
	return false;
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testGoodClassDef() {
		$code = <<<EOD
class Foo {
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testGoodAbstractClassDef() {
		$code = <<<EOD
abstract class Foo {
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testBadClassDef() {
		$code = <<<EOD
class Foo  {
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testClassWithExtends() {
		$code = <<<EOD
class Foo extends \bar {
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testIncorrectClassWithExtends() {
		$code = <<<EOD
class Foo  extends \bar {
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testMultipleExtends() {
		$code = <<<EOD
class FileRecord extends Record implements Iterator, ArrayAccess {
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testEmptyExtends() {
		$code = <<<EOD
class FileRecord extends Record implements {
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testForWithSemicolons() {
		$code = <<<EOD
for (1; 2; 3) {
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testForShortSemicolonList() {
		$code = <<<EOD
for (1; 2) {
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testMultiSemicolonFor() {
		$code = <<<EOD
for (\$i = 0, \$total = count(\$arr); \$i < 10; \$i++) {
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}

	public function testForExtraSpacing() {
		$code = <<<EOD
for (1;  2; 3) {
}
EOD;
		$this->assertRuleFail($code, $this->rule);
	}

	public function testCorrectControlStructureWithBracketsInExpression() {
		$code = <<<EOD
if (\$data->{\$key}) {
	return false;
}
EOD;
		$this->assertRulePass($code, $this->rule);
	}
}
?>