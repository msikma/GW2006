// Gaming World 2006 <https://gamingw.net/>
// © MIT License

/**
 * Callback that selects all threads or messages in the current form.
 * 
 * Optionally this is filtered by a required name (in which case the name and id attributes will be checked against).
 * 
 * Used by the quick moderation tools to select threads to moderate.
 */
export function selectCheckAll($checkbox, $form, requiredName, ignoreDisabled = true) {
  // The value that all checkboxes should be set to.
  const isChecked = $checkbox.checked

  for (let n = 0; n < $form.length; ++n) {
    const $item = $form[n]
    const hasName = 'name' in $item
    const matchesName = !requiredName || (hasName && ($item.name.startsWith(requiredName) || $item.id.startsWith(requiredName)))
    const isDisabled = $item.disabled
    const isCheckbox = $item.type === 'checkbox'
    
    // Skip if this item does not match our requirements.
    if (!isCheckbox || !hasName || !matchesName || (ignoreDisabled && isDisabled)) {
      continue
    }

    $item.checked = isChecked
    highlightTr($item)
  }
}

/**
 * Callback when selecting a thread or message.
 * 
 * Used by the quick moderation tools to select threads to moderate.
 */
export function selectCheck($checkbox) {
  highlightTr($checkbox)
}

/**
 * Adds the .is_selected class to a checkbox's containing <tr> tag.
 */
function highlightTr($checkbox) {
  const $tr = $checkbox.closest('tr')
  $tr.classList.toggle('is_selected', $checkbox.checked)
}
