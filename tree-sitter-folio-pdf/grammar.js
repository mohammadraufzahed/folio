module.exports = grammar({
  name: 'folio_pdf',

  rules: {
    document: $ => repeat($._statement),

    _statement: $ => choice(
      $.element,
      $.directive,
      $.control_structure,
      $.comment
    ),

    element: $ => choice(
      $.page_element,
      $.column_element,
      $.row_element,
      $.text_element,
      $.heading_element
    ),

    page_element: $ => seq(
      'page',
      optional($.attributes),
      $.block
    ),

    column_element: $ => seq(
      'column',
      optional($.attributes),
      $.block
    ),

    row_element: $ => seq(
      'row',
      optional($.attributes),
      $.block
    ),

    text_element: $ => seq(
      'text',
      optional($.attributes),
      optional($.string)
    ),

    heading_element: $ => seq(
      'heading',
      optional($.attributes),
      optional($.string)
    ),

    block: $ => seq(
      '{',
      repeat($._statement),
      '}'
    ),

    attributes: $ => seq(
      '(',
      repeat1($.attribute),
      ')'
    ),

    attribute: $ => seq(
      $.identifier,
      '=',
      choice($.string, $.number, $.identifier)
    ),

    directive: $ => seq(
      '@',
      $.identifier,
      optional(choice($.string, $.number, $.identifier))
    ),

    control_structure: $ => choice(
      $.if_statement,
      $.foreach_statement
    ),

    if_statement: $ => seq(
      'if',
      $.expression,
      $.block,
      optional(seq('else', $.block))
    ),

    foreach_statement: $ => seq(
      'foreach',
      $.identifier,
      'as',
      $.identifier,
      $.block
    ),

    expression: $ => choice(
      $.identifier,
      $.string,
      $.number,
      $.comparison,
      $.logical_expression
    ),

    comparison: $ => seq(
      $.expression,
      choice('==', '!=', '<', '<=', '>', '>='),
      $.expression
    ),

    logical_expression: $ => seq(
      $.expression,
      choice('&&', '||'),
      $.expression
    ),

    string: $ => /"[^"]*"/,

    number: $ => /\d+(\.\d+)?/,

    identifier: $ => /[a-zA-Z_][a-zA-Z0-9_]*/,

    comment: $ => /\/\/[^\n]*/
  }
});
