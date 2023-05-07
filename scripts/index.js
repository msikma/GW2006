// Gaming World 2006 <https://gamingw.net/>
// © MIT License

import * as quickMod from './lib/quick_mod.js'
import * as componentTimestamp from './lib/components/timestamp.js'
import * as componentAvatar from './lib/components/avatar.js'

const gw = {
  ...quickMod,
  ...componentTimestamp,
  ...componentAvatar,
}

window.gw = gw
