// Gaming World 2006 <https://gamingw.net/>
// © MIT License

import {getYoutubeId, createYoutubeEmbed} from '../util/embeds.js'

/**
 * Interactivity for a single post item, e.g. a post thread, or a reply, or a post history item.
 */
export function decoratePostItem(id) {
  // There should only ever be a single instance of a post on a page, but...just in case.
  const postItems = document.querySelectorAll(`.post_item.post_id_${id}`)
  postItems.forEach(postItem => {
    if (postItem._isDecorated) {
      return
    }
    postItem._isDecorated = true

    const postText = postItem.querySelector('.inner.post_text')

    // Check if this post contains Youtube URLs. Replace those into embeds.
    const ytLinks = postText.querySelectorAll('a.bbc_link[href*="youtu.be"], a.bbc_link[href*="youtube.com"]')
    ytLinks.forEach(ytLink => {
      const content = ytLink.innerText.trim()
      const href = ytLink.getAttribute('href').trim()
      if (content !== href) {
        return
      }
      // The link is a plain [url]https://www.youtube.com/watch?v=oMBye719ayA[/url] link,
      // or a plaintext link that got converted into one automatically.
      // In this case we'll convert it into an embed.
      const youtubeId = getYoutubeId(href)
      const embedNode = document.createElement('div')
      embedNode.classList.add('gwbbc', 'gwbbc_youtube')
      embedNode.setAttribute('data-content', youtubeId)
      embedNode.innerHTML = createYoutubeEmbed(youtubeId)
      ytLink.parentNode.replaceChild(embedNode, ytLink)
    })

    // If this is an ignored post, decorate its unhide link.
    if (postItem.className.includes('is_ignore')) {
      postItem.querySelector('.unhide a').addEventListener('click', ev => {
        ev.preventDefault()
        postItem.classList.add('display_ignored_post')
      })
    }
  })
}
