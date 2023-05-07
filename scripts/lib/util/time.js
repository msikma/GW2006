// Gaming World 2006 <https://gamingw.net/>
// © MIT License

/**
 * Returns a relative timestamp between a given timestamp and now.
 */
export function getRelativeTime(timestamp, now = new Date()) {
  const diffMilliseconds = timestamp.getTime() - now.getTime()
  const diffSeconds = Math.floor(diffMilliseconds / 1000)
  const [value, unit] = getRelativeTimeUnit(diffSeconds)
  const timeFormatter = new Intl.RelativeTimeFormat('en', {numeric: 'auto'})
  return timeFormatter.format(value, unit)
}

/**
 * Returns the closest time unit to a given time delta.
 */
function getRelativeTimeUnit(timeSeconds) {
  const deltaSeconds = Math.abs(timeSeconds)
  const units = Object.entries({
    now: 1,
    second: 60,
    minute: 3600,
    hour: 86400,
    day: 86400 * 7,
    week: 86400 * 30,
    month: 86400 * 365,
    year: Infinity
  })
  const idx = units.findIndex(([_, cutoff]) => cutoff >= deltaSeconds)
  const unit = units[idx][0]
  const divisor = units[Math.max(idx - 1, 0)][1]

  // If the difference is less than a second, just display "now".
  if (unit === 'now') {
    return [0, 'second']
  }

  return [Math.floor(timeSeconds / divisor), unit]
}
