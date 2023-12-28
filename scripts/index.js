// Gaming World 2006 <https://gamingw.net/>
// © MIT License

import * as quickMod from './lib/quick_mod.js'
import * as componentAvatar from './lib/components/avatar.js'
import * as componentBanForm from './lib/components/ban-form.js'
import * as componentGwCaptcha from './lib/components/gw-captcha.js'
import * as componentPostItem from './lib/components/post-item.js'
import * as componentTimestamp from './lib/components/timestamp.js'

const gw = {
  ...quickMod,
  ...componentAvatar,
  ...componentBanForm,
  ...componentGwCaptcha,
  ...componentPostItem,
  ...componentTimestamp,
}

window.gw = gw
