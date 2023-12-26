// Gaming World 2006 <https://gamingw.net/>
// © MIT License

import * as quickMod from './lib/quick_mod.js'
import * as componentTimestamp from './lib/components/timestamp.js'
import * as componentAvatar from './lib/components/avatar.js'
import * as componentGwCaptcha from './lib/components/gw-captcha.js'
import * as componentPostItem from './lib/components/post-item.js'

const gw = {
  ...quickMod,
  ...componentTimestamp,
  ...componentAvatar,
  ...componentGwCaptcha,
  ...componentPostItem,
}

window.gw = gw
