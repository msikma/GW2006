// Gaming World 2006 <https://gamingw.net/>
// © MIT License

/**
 * Decorates the captcha on the register page.
 */
export function decorateGwCaptcha() {
  const captcha = document.querySelector('.gw_captcha')
  const input = captcha.querySelector('input[name="gw_captcha_value"]')
  const state = {
    occupied: {},
    slots: []
  }

  function updateValue() {
    const values = []
    for (const item of Object.values(state.occupied)) {
      const id = item ? item.id.split('_') : []
      values.push(id[1])
    }
    const value = values.filter(val => val).map(val => String(val).padStart(2, '0')).join(',')
    input.value = value
  }

  function getNextSlot() {
    for (let n = 0; n < state.slots.length; ++n) {
      if (!state.occupied[n]) {
        return n
      }
    }
    return null
  }

  function isCompleted() {
    for (let n = 0; n < state.slots.length; ++n) {
      if (!state.occupied[n]) {
        return false
      }
    }
    return true
  }

  function updateIndicator() {
    const done = isCompleted()
    let next = getNextSlot()
    if (next === null) {
      next = done ? null : 0
    }
    state.slots.forEach(slot => slot.classList.remove('selected'))
    if (next !== null) {
      state.slots[next].classList.add('selected')
    }
    state.slots[state.slots.length - 1].classList.toggle('done', done)
  }

  const slots = [...captcha.querySelectorAll('.selection > td')]
  slots.forEach((slot, n) => slot.addEventListener('click', ev => {
    state.slots[n].innerHTML = ''
    state.occupied[n].classList.remove('selected')
    state.occupied[n].setAttribute('data-slot', null)
    state.occupied[n] = false
    updateIndicator()
    updateValue()
  }))
  
  const options = [...captcha.querySelectorAll('.options > a')]
  options.forEach(option => option.addEventListener('click', ev => {
    ev.preventDefault()
    const node = ev.target.tagName === 'IMG' ? ev.target : ev.target.querySelector(':scope > img')
    if (node.classList.contains('selected')) {
      const position = node.getAttribute('data-slot')
      state.slots[position].click()
      return
    }
    const next = getNextSlot()
    if (next === null) {
      return
    }
    node.classList.add('selected')
    node.setAttribute('data-slot', next)
    state.slots[next].innerHTML = node.outerHTML
    state.occupied[next] = node
    updateIndicator()
    updateValue()
  }))

  state.slots = slots
  updateIndicator()
  updateValue()
}
