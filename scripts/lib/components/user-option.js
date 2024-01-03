// Gaming World 2006 <https://gamingw.net/>
// © MIT License

/**
 * Decorates options on the user settings pages.
 */
export function decorateUserOption(slug) {
  const node = document.querySelector(`#slug_${slug}`)

  // The avatar customization option lets users change their avatar's display,
  // either making it retina (high dpi) or pixel art (turn off smoothing).
  // This code only covers the preview on the user options page.
  if (slug === 'cust_avatar') {
    // Determine the avatar image's dimensions for the retina preview.
    const avatarImg = document.querySelector('#avatar_upload img')
    const avatarState = {isRetina: false, width: null, height: null}
    const updateAvatar = () => {
      const {width, height, isRetina} = avatarState
      if (width) {
        avatarImg.width = (width / (isRetina ? 2 : 1))
        avatarImg.height = (height / (isRetina ? 2 : 1))
      }
    }
    avatarImg.addEventListener('load', ev => {
      avatarState.width = avatarImg.width
      avatarState.height = avatarImg.height
      updateAvatar()
    })

    // Listen for changes to the setting <select> field itself.
    const select = node.querySelector(':scope > select')
    const options = {'default': 0, 'retina': 1, 'pixelart': 2}
    const changeOption = () => {
      const value = Number(select.value)
      avatarState.isRetina = value === options.retina
      avatarImg.classList.toggle('pixel', value === options.pixelart)
      updateAvatar()
    }

    node.addEventListener('change', ev => {
      ev.preventDefault()
      changeOption()
    })

    changeOption(select.value)
  }
}
