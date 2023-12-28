// Gaming World 2006 <https://gamingw.net/>
// © MIT License

import {findDecorationElement, callOnImgLoad} from '../util/nodes.js'

/**
 * Decorates a user avatar.
 */
export function decorateAvatar() {
  const el = findDecorationElement('avatar')
  const img = el.querySelector('img')
  const isRetina = el.getAttribute('data-is-retina') === 'yes'
  const isPixelArt = el.getAttribute('data-is-pixelart') === 'yes'

  if (isRetina) {
    callOnImgLoad(img, (ev, scale = 2) => {
      const width = img.naturalWidth / scale
      const height = img.naturalHeight / scale
      img.setAttribute('width', Math.round(width))
      img.setAttribute('height', Math.round(height))
    })
  }
  if (isPixelArt) {
    // Nothing for now. The image will have a "pixel" class and that's enough.
  }
}
