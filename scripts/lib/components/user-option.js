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

  // Allows the user to customize the color of their member name and pips.
  if (slug === 'cust_member') {
    // Add a preview next to the <select> box.
    const select = node.querySelector(':scope > select')
    const pipPreview = document.createElement('div')
    pipPreview.classList.add('preview')
    pipPreview.innerHTML = `<span class="name">Name</span><span class="pips pip_type_lego"></span>`
    const nameContainer = pipPreview.querySelector(':scope > .name')
    const pipContainer = pipPreview.querySelector(':scope > .pips')
    select.parentNode.insertBefore(pipPreview, select.nextSibling)

    const type = 'lego'
    const colors = {0: 'gold', 1: 'pink', 2: 'mint'}

    const setPipPreview = () => {
      const value = select.value
      const color = colors[value]
      const pips = Array(5).fill().map(_ => `<img src="${window.gwContext.images_url}/pips/${type}_${color}.png" class="pixel">`)
      pipContainer.innerHTML = pips.join('')
      nameContainer.setAttribute('class', '')
      nameContainer.classList.add('name', `pip_color_${color}`)
    }

    node.addEventListener('change', ev => {
      ev.preventDefault()
      setPipPreview()
    })

    setPipPreview()
  }
}
