/**
 * Defines some used polyfills.
 *
 * @author Roman Bauer.
 * @since  1.0.0
 */
  
/**
 * Polyfill for Number.isInteger() of ECMAScript 6.
 */
Number.isInteger = Number.isInteger || function(value) {
  return typeof value === 'number' && isFinite(value) && Math.floor(value) === value;
};

/**
 * Polyfill for Number.isNaN() of ECMAScript 6.
 */
Number.isNaN = Number.isNaN() || function(value) {
    var n = Number(value);
    return n !== n;
};