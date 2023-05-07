// Gaming World 2006 <https://gamingw.net/>
// © MIT License

/**
 * Runs a callback on image load.
 */
export function callOnImgLoad(img, fn) {
  if (img.complete) {
    fn(null)
  }
  else {
    img.addEventListener('load', fn)
  }
}

/**
 * Locates the element nearest our current script for decoration purposes.
 * 
 * This traverses the current script's siblings until it finds the element we want.
 * In practice it's always going to be the previous element, as we normally decorate
 * an element immediately after creating it in the HTML.
 */
export function findDecorationElement(type, mandatory = true, currentScript = document.currentScript) {
  let el = currentScript.previousElementSibling
  while (el) {
    if (el.classList.contains(`gw_${type}`)) {
      return el
    }
    el = el.previousElementSibling
  }
  // If we can't find the item, throw an error.
  // In practice this means the element remains undecorated as plain HTML.
  if (mandatory) {
    console.error(`No decoration element found for the given script: %o`, currentScript)
    throw new Error(`gw: Could not decorate element of type "${type}"`)
  }
  return null
}
