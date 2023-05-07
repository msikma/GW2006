// Gaming World 2006 <https://gamingw.net/>
// © MIT License

import {findDecorationElement, callOnImgLoad} from '../util/nodes.js'

/**
 * Decorates a user avatar.
 */
export function decorateAvatar() {
  const el = findDecorationElement('avatar')
  const img = el.querySelector('img')
  const isRetina = el.getAttribute('data-avatar-retina') === 'retina'

  const setRetinaScale = (ev, scale = 2) => {
    const width = img.naturalWidth / scale
    const height = img.naturalHeight / scale
    img.style.width = `${width}px`
    img.style.height = `${height}px`
  }

  if (isRetina) {
    callOnImgLoad(img, setRetinaScale)
  }
}
