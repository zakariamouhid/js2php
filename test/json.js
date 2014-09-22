/*global global, testSuite, Buffer*/
(function() {

  testSuite('json', function(assert) {

    testSuite('stringify', function() {
      assert('string primitive', JSON.stringify('s') === '"s"');
      var a = [true,false, 1, null, undefined, NaN, Infinity, -Infinity, 's'];
      assert('primitives', JSON.stringify(a) === '[true,false,1,null,null,null,null,null,"s"]');
      a = new Array(3);
      assert('empty array', JSON.stringify(a) === '[null,null,null]');
      a = [new String('s'), new Boolean(false), new Number(3)];
      assert('boxed primitives', JSON.stringify(a) === '["s",false,3]');
      var s = '\\"How\tdaft\njumping\fzebras\rvex"';
      assert(
        'control characters',
        JSON.stringify(s) === '"\\\\\\"How\\tdaft\\njumping\\fzebras\\rvex\\""'
      );
    });

  });

})();