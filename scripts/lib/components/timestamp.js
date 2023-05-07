// Gaming World 2006 <https://gamingw.net/>
// © MIT License

import {findDecorationElement} from '../util/nodes.js'
import {getRelativeTime} from '../util/time.js'

/**
 * Returns whether a timestamp is recent (within 24 hours of now).
 */
function isRecent(timestamp) {
  // Jump 24 hours into the past.
  const past = new Date().getTime() - (24 * 3600 * 1000)
  
  return past < timestamp.getTime()
}

/**
 * Returns the HTML for the timestamp element.
 */
function getTimestampHTML(relativeTime, regularTime) {
  const times = []
  times.push(`<span class="regular">${regularTime}</span>`)
  if (relativeTime) {
    times.push(`<span class="relative">${relativeTime}</span>`)
  }
  return times.join('')
}

/**
 * Decorates a timestamp atom.
 * 
 * This causes it to display the relative time, if needed.
 */
export function decorateTimestamp() {
  if (!Intl.RelativeTimeFormat) {
    return
  }
  const el = findDecorationElement('timestamp')
  const timestamp = new Date(Number(el.getAttribute('data-timestamp')) * 1000)
  const showRelative = isRecent(timestamp)

  // If we're not showing relative time, just display the original content.
  if (!showRelative) {
    return
  }

  // This timestamp is from the last 24 hours, so we'll replace it with relative time.
  const originalContent = el.innerHTML
  const update = () => {
    // If the timestamp stops being recent, reset it with the original full timestamp.
    if (!isRecent(timestamp)) {
      clearInterval(updateInterval)
      el.innerHTML = getTimestampHTML(null, originalContent)
      return
    }
    // Update the content.
    el.innerHTML = getTimestampHTML(`${getRelativeTime(timestamp)}`, originalContent)
  }
  const updateInterval = setInterval(update, 5000)
  update()
}
