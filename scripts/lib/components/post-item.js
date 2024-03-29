// Gaming World 2006 <https://gamingw.net/>
// © MIT License

import {getYoutubeAttributes, createYoutubeEmbed} from '../util/embeds.js'

/**
 * Interactivity for a single post item, e.g. a post thread, or a reply, or a post history item.
 * 
 * Also used for decorating a user signature on the member profile page.
 */
export function decoratePostItem(id, memberId) {
  // Either select a regular post, or a member's signature by their ID.
  const selector = id ? `.post_item.post_id_${id}` : `.member_id_${memberId}`

  // There should only ever be a single instance of a post on a page, but...just in case.
  const postItems = document.querySelectorAll(selector)

  postItems.forEach(postItem => {
    if (postItem._isDecorated) {
      return
    }
    postItem._isDecorated = true

    const postText = postItem.querySelector('.inner.post_text')
    const sigText = postItem.querySelector('.signature.post_text')

    // Add a CSS class to all images that fail to load.
    const bbcImages = [
      ...postText ? postText.querySelectorAll('.bbc_img') : [],
      ...sigText ? sigText.querySelectorAll('.bbc_img') : []
    ]
    bbcImages.forEach(img => img.addEventListener('error', ev => {
      ev.target.classList.add('bbc_img_error')
    }))

    // Check if this post contains Youtube URLs. Replace those into embeds.
    // We don't replace the links in the signature since there's not enough space.
    const ytLinks = postText ? postText.querySelectorAll('a.bbc_link[href*="youtu.be"], a.bbc_link[href*="youtube.com"]') : []
    
    ytLinks.forEach(ytLink => {
      const content = ytLink.innerText.trim()
      const href = ytLink.getAttribute('href').trim()
      if (content !== href) {
        return
      }
      // The link is a plain [url]https://www.youtube.com/watch?v=oMBye719ayA[/url] link,
      // or a plaintext link that got converted into one automatically.
      // In this case we'll convert it into an embed.
      const youtubeAttributes = getYoutubeAttributes(href)
      if (!youtubeAttributes.meta.isYoutubeUrl) {
        return
      }
      const embedNode = document.createElement('div')
      embedNode.classList.add('gwbbc', 'gwbbc_youtube')
      embedNode.setAttribute('data-content', youtubeAttributes.id)
      embedNode.innerHTML = createYoutubeEmbed(youtubeAttributes.id, youtubeAttributes.attributes, youtubeAttributes.meta)
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
