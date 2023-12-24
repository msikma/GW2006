// Gaming World 2006 <https://gamingw.net/>
// © MIT License

const YOUTUBE_BASE_REGULAR = 'https://www.youtube.com/embed/'
const YOUTUBE_BASE_PRIVACY = 'https://www.youtube-nocookie.com/embed/'
const YOUTUBE_EMBED_ATTRIBUTES = [
  //'autoplay',
  'cc_lang_pref',
  'cc_load_policy',
  'color',
  'controls',
  //'disablekb',
  //'enablejsapi',
  'end',
  'fs',
  'hl',
  'iv_load_policy',
  'list',
  'listType',
  'loop',
  'modestbranding',
  //'origin',
  'playlist',
  'playsinline',
  'rel',
  'start',
  'v',
  //'widget_referrer',
]

/**
 * Extracts and returns the Youtube id and other attributes from a URL.
 * 
 * If no ID is found or the URL is invalid, null is returned instead.
 * 
 * This also supports /videoseries URLs, although it does not support
 * various other URL forms like /playlist.
 */
export function getYoutubeAttributes(url) {
  if (!url) {
    return null
  }

  const data = {
    // The video ID.
    id: null,
    // Additional attributes, such as e.g. "hl" for the interface language, etc.
    attributes: {},
    // All additional options.
    meta: {
      // Whether this is a "videoseries" URL (for embedding playlists).
      isVideoSeries: false,
      // Whether this is a valid URL we can display as a Youtube embed.
      isYoutubeUrl: true
    }
  }

  const parsed = new URL(url)
  const params = parsed.searchParams

  // Video ID; plain links will have this in the "v" parameter.
  data.id = params.get('v')

  // Extract data from the URL path section. For /embed/id URLs, this sets the video ID.
  const path = parsed.pathname.slice(1).split('/')
  if (path[1]) {
    if (path[1] === 'videoseries') {
      data.meta.isVideoSeries = true
    }
    else {
      data.id = path[1]
    }
  }

  for (const attr of YOUTUBE_EMBED_ATTRIBUTES) {
    if (params.has(attr)) {
      data.attributes[attr] = params.get(attr)
    }
  }

  if (!data.id && !data.meta.isVideoSeries) {
    data.meta.isYoutubeUrl = false
  }

  return data
}

/**
 * Returns a Youtube embed string.
 */
export function createYoutubeEmbed(youtubeId = null, embedAttributes = {}, embedMeta = {}, userOptions = {}) {
  const options = {
    // Width and height; one can be 'auto' to scale to the given ratio.
    width: 560,
    height: 'auto',
    ratio: 16 / 9,
    // Whether to serve the embed from the cookieless domain.
    usePrivacyEnhanced: true,
    // Various other options.
    embedTitle: 'Youtube video player',
    allowedFeatures: 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share',
    allowedFullscreen: true,
    ...userOptions
  }

  // Put together the embed URL.
  const url = new URL([
    !options.usePrivacyEnhanced ? YOUTUBE_BASE_REGULAR : YOUTUBE_BASE_PRIVACY,
    !embedMeta.isVideoSeries ? youtubeId : 'videoseries'
  ].join(''))
  for (const [attr, value] of Object.entries(embedAttributes)) {
    url.searchParams.set(attr, value)
  }

  const width = options.width === 'auto' ? options.height * (options.ratio) : options.width
  const height = options.height === 'auto' ? options.width / (options.ratio) : options.height

  return `
    <iframe
      width="${width}"
      height="${height}"
      src="${url.toString()}"
      title="${options.embedTitle}"
      frameborder="0"
      allow="${options.allowedFeatures}"
      ${options.allowedFullscreen ? 'allowfullscreen' : ''}>
    </iframe>
  `
}
