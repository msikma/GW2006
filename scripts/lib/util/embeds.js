// Gaming World 2006 <https://gamingw.net/>
// © MIT License

/**
 * Extracts and returns the Youtube id from a URL.
 * 
 * If no ID is found or the URL is invalid, null is returned instead.
 */
export function getYoutubeId(url) {
  if (!url) {
    return null
  }
  const parsed = new URL(url)
  const id = parsed.searchParams.get('v')
  if (!id) {
    return null
  }
  return id
}

/**
 * Returns a Youtube embed string.
 */
export function createYoutubeEmbed(youtubeId, userOptions) {
  const options = {
    // Width and height; one can be 'auto' to scale to the given ratio.
    width: 560,
    height: 'auto',
    ratio: 16 / 9,
    // Whether to serve the embed from the cookieless domain.
    usePrivacyEnhanced: false,
    // Various other options.
    embedTitle: 'Youtube video player',
    allowedFeatures: 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share',
    allowedFullscreen: true,
    ...userOptions
  }

  // Uses either the regular tracking Youtube domain, or the cookieless alternative domain.
  const baseURLs = {
    regular: 'https://www.youtube.com/embed/',
    privacyEnhanced: 'https://www.youtube-nocookie.com/embed/'
  }

  const width = options.width === 'auto' ? options.height * (options.ratio) : options.width
  const height = options.height === 'auto' ? options.width / (options.ratio) : options.height

  return `
    <iframe
      width="${width}"
      height="${height}"
      src="${baseURLs[options.usePrivacyEnhanced ? 'privacyEnhanced' : 'regular']}${youtubeId}"
      title="${options.embedTitle}"
      frameborder="0"
      allow="${options.allowedFeatures}"
      ${options.allowedFullscreen ? 'allowfullscreen' : ''}>
    </iframe>
  `
}
