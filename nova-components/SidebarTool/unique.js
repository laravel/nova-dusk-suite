let postcss = require('postcss')
let fs = require('fs')

module.exports = (options = {}) => {
  let selectors = new Set()

  postcss.parse(fs.readFileSync(options.path)).walkRules(rule => {
    selectors.add(rule.selector)
  })

  return {
    postcssPlugin: 'unique',

    Rule(rule) {
      if (selectors.has(rule.selector)) {
        rule.remove()
      }
    },
  }
}

module.exports.postcss = true
