// Gaming World 2006 <https://gamingw.net/>
// © MIT License

/**
 * Interactivity for a single post item, e.g. a post thread, or a reply, or a post history item.
 * 
 * Also used for decorating a user signature on the member profile page.
 */
export function decorateBanForm() {
  const form = document.querySelector('#manage_bans')
  
  // The form has two sections: creating a new ban group, and adding a user to an existing one.
  const createNewBan = form.querySelector('#new_ban_group')
  const existingBanGroups = form.querySelectorAll('.existing_ban_group')

  // Selector for whether we're adding someone to a ban group or not.
  const typeSelector = form.querySelector('#ban_group')

  // Whether the group ban suggestions mod is active.
  const hasGroupBanSuggestions = form.classList.contains('has_group_ban_suggestions')

  /** Toggles between creating a new ban group or adding to an existing. */
  const toggleSection = type => {
    createNewBan.style.display = type === 'new' ? `table-row-group` : `none`
    existingBanGroups.forEach(existingBanGroup => {
      const value = Number(existingBanGroup.getAttribute('name'))
      existingBanGroup.style.display = (type !== 'new') && (type === value) ? `table-row-group` : `none`
    })
  }

  typeSelector.addEventListener('change', ev => {
    const value = Number(ev.target.value)
    toggleSection(value < 0 ? 'new' : value)
  })

  form.addEventListener('change', ev => {
    const isFullBan = form.querySelector('#full_ban').checked
    form.querySelector('#expire_date').disabled = !form.querySelector('#expires_one_day').checked
    form.querySelector('#cannot_post').disabled = isFullBan
    form.querySelector('#cannot_register').disabled = isFullBan
    form.querySelector('#cannot_login').disabled = isFullBan
  })
}
