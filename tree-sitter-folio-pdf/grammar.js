module.exports = grammar({
  name: 'folio_pdf',

  extras: $ => [
    /\s/,
    $.comment,
  ],

  rules: {
    document: $ => repeat($._statement),

    _statement: $ => choice(
      $.page_chrome,
      $.var_decl,
      $.partial,
      $.element,
      $.control_structure,
      $.directive,
      $.comment
    ),

    page_chrome: $ => seq(
      field('kind', choice('pageheader', 'pagefooter')),
      optional($.attributes),
      optional($.block)
    ),

    var_decl: $ => seq(
      choice('var', 'prop'),
      $.identifier,
      '=',
      $._value
    ),

    partial: $ => seq(
      'partial',
      choice($.string, $.identifier)
    ),

    element: $ => choice(
      $.page_element,
      $.column_element,
      $.row_element,
      $.text_element,
      $.heading_element,
      $.table_element,
      $.table_row_element,
      $.table_cell_element,
      $.chrome_widget
    ),

    page_element: $ => seq('page', optional($.attributes), optional($.block)),
    column_element: $ => seq('column', optional($.attributes), optional($.block)),
    row_element: $ => seq('row', optional($.attributes), optional($.block)),
    text_element: $ => seq('text', optional($.attributes), optional($._value)),
    heading_element: $ => seq('heading', optional($.attributes), optional($._value)),

    table_element: $ => seq('table', optional($.attributes), optional($.block)),
    table_row_element: $ => seq(
      choice('tr', 'header', 'footer'),
      optional($.attributes),
      optional($.block)
    ),
    table_cell_element: $ => seq(
      choice('th', 'td'),
      optional($.attributes),
      optional($._value),
      optional($.block)
    ),

    chrome_widget: $ => seq(
      choice('monogram', 'badge', 'spacer', 'rule', 'box', 'pagenum', 'img'),
      optional($.attributes),
      optional($._value),
      optional($.block)
    ),

    block: $ => seq('{', repeat($._statement), '}'),

    attributes: $ => seq(
      '(',
      optional(seq($.attribute, repeat(seq(',', $.attribute)), optional(','))),
      ')'
    ),

    attribute: $ => seq(
      field('name', $.identifier),
      '=',
      field('value', $._value)
    ),

    _value: $ => choice(
      $.string,
      $.number,
      $.property_access,
      $.identifier
    ),

    property_access: $ => prec.left(seq(
      $.identifier,
      repeat1(seq('.', $.identifier))
    )),

    directive: $ => seq('@', $.identifier, optional($._value)),

    control_structure: $ => choice(
      $.if_statement,
      $.foreach_statement
    ),

    if_statement: $ => seq(
      'if',
      $._value,
      $.block,
      optional(seq('else', $.block))
    ),

    foreach_statement: $ => seq(
      'foreach',
      choice($.property_access, $.identifier),
      'as',
      $.identifier,
      $.block
    ),

    string: $ => /"([^"\\]|\\.)*"/,
    number: $ => /\d+(\.\d+)?/,
    identifier: $ => /[a-zA-Z_][a-zA-Z0-9_]*/,
    comment: $ => token(seq('//', /[^\n]*/)),
  }
});
