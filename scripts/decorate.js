// Gaming World 2006 <https://gamingw.net/>
// © MIT License

const state = {
  messageNode: null,
  emoticonPopupWindow: null,
  emoticon_path: null,
  emoticons: [],
}

/** Will contain a subset of the SMF context. */
const context = {
}

/**
 * The primary decoration function.
 * 
 * This function is called a number of times on the page immediately after a dynamic element is printed.
 */
function decorateComponent(type, ...args) {
  const types = {
    loginForm: decorateLoginForm,
    messageField: decorateMessageField,
    postItem: decoratePostItem,
    permissionsFilter: decoratePermissionsFilter,
    popupWindow: decoratePopupWindow,
    posticons: decoratePostIcons,
    searchFormBoards: decorateSearchFormBoards,
    whoFilter: decorateWhoIsOnline
  }
  const runDecorator = types[type] || unknownDecorator

  runDecorator(type, ...args)
}

/**
 * Interactivity for a single post item, e.g. a post thread, or a reply, or a post history item.
 */
function decoratePostItem(_, id) {
  // There should only ever be a single instance of a post on a page, but...just in case.
  const $postItems = document.querySelectorAll(`.post_item.post_id_${id}`)
  $postItems.forEach($postItem => {
    // Again, this should never be needed. Protection in case we double decorate a post.
    if ($postItem._isDecorated) {
      return
    }
    $postItem._isDecorated = true

    // If this is an ignored post, decorate its unhide link.
    if ($postItem.className.includes('is_ignore')) {
      $postItem.querySelector('.unhide a').addEventListener('click', ev => {
        ev.preventDefault()
        $postItem.classList.add('display_ignored_post')
      })
    }
  })
}

/**
 * Allows the user to pick whether to search through the entire forum or a specific set of boards.
 * 
 * Part of the advanced search page.
 */
function decorateSearchFormBoards() {
  const $boardSpecific = document.querySelector('.search_form_boards_selection #check_specific')
  const $targetCheckboxes = document.querySelectorAll('.search_form_boards_selection input[type="radio"]')
  const $listContainer = document.querySelector('.search_subforums')
  const $categoryCheckboxes = document.querySelectorAll('.category input')
  $categoryCheckboxes.forEach(el => el.indeterminate = true)

  /** Extracts the depth number from the class value of a <label>. */
  const getDepth = label => {
    const labelClasses = String(label.classList).split(/\s+/)
    const depthClass = labelClasses.find(cls => cls.startsWith('depth_'))
    if (!depthClass == null) return null
    return Number(depthClass.split('_')[1])
  }

  /** Returns all of a given checkbox's child checkboxes (which are actually siblings in the DOM). */
  const getDeeperCheckboxes = checkbox => {
    const li = checkbox.closest('li')
    const label = li.querySelector('label')
    const depth = getDepth(label)

    let cLi = li
    const deeperCheckboxes = []
    while (true) {
      cLi = cLi.nextElementSibling
      const cLabel = cLi.querySelector('label')
      const cDepth = getDepth(cLabel)
      if (cDepth <= depth) {
        break
      }
      deeperCheckboxes.push([cLi, cLi.querySelector('input')])
    }
    
    return deeperCheckboxes
  }

  // List of actual board checkboxes.
  const $boardCheckboxes = document.querySelectorAll('.forum_hierarchy input')
  $boardCheckboxes.forEach(input => input.addEventListener('change', ev => {
    const isChecked = ev.originalTarget.checked
    const deeperCheckboxes = getDeeperCheckboxes(ev.originalTarget)
    deeperCheckboxes.forEach(([_, check]) => check.checked = isChecked)
  }))

  $boardCheckboxes.forEach(input => input.checked = false)

  /** Toggles the boards list on/off. */
  const toggleBoards = setVisibility => {
    $listContainer.style.display = setVisibility ? 'table-row' : 'none'
  }

  if ($boardSpecific.checked) {
    toggleBoards(true)
  }

  $targetCheckboxes.forEach(input => input.addEventListener('change', ev => {
    const isVisible = ev.originalTarget.value === 'forums'
    toggleBoards(isVisible)
  }))
}

/**
 * Placeholder for decorators that are not implemented yet.
 */
function unknownDecorator(type, ...args) {
  console.log(`Decorator not implemented yet: ${type} - Called with arguments: `, args)
}

function decorateLoginForm(username, sessionID) {
  const adminField = document.querySelector('.login_form input[name="admin_pass"]')
  const userField = document.querySelector('.login_form input[name="user"]')
  const field = adminField || userField
  field.focus()
}

function decoratePopupWindow() {
  const button = document.querySelector('#popup_window_close')
  button.addEventListener('click', _ => window.close())
}

/**
 * Adds the callback for the "who is online" switcher.
 */
function decorateWhoIsOnline() {
  const form = document.querySelector('#who_is_online_filter')
  const switcher = document.querySelector('#show_top')

  switcher.addEventListener('change', ev => {
    ev.preventDefault()
    const radio = ev.originalTarget
    form.submit()
    // const base = radio.getAttribute('data-basename')
    // const url = `${baseURL}/${base}`
    // image.setAttribute('src', url)
  });
  //<select name="show_top" onchange="document.forms.whoFilter.show.value = this.value; document.forms.whoFilter.submit();">';
}

/**
 * Adds the callback for the forum specific permissions switcher.
 */
function decoratePermissionsFilter() {
  const form = document.querySelector('#board_permissions_filter')
  const switcher = document.querySelector('#show_board')

  switcher.addEventListener('change', ev => {
    ev.preventDefault()
    const radio = ev.originalTarget
    form.submit()
  });
}

/**
 * Inserts an emoticon into the message node.
 */
function insertEmoticonToMessage(emoticonName) {
  const emoticon = state.emoticons[emoticonName]
  const code = emoticon.codes[0]
  
  insertAtCursorPosition(state.messageNode, code)
}

/**
 * Inserts a string into a <textarea> node at the current cursor position.
 */
function insertAtCursorPosition(node, str) {
  const start = node.selectionStart
  const end = node.selectionEnd

  node.value = [
    node.value.substring(0, start),
    str,
    node.value.substring(end, node.value.length)
  ].join('')
}

function getEmoticonsPopupContent() {

}

/**
 * Generates the emoticons popup HTML.
 */
function generateEmoticonsPopup() {
  const txt = {
    title: `More Emoticons - ${context.forum_name}`
  }

  // Collect all emoticons and sort them into groups.
  const emoticonGroups = {}
  for (const emoticon of Object.values(state.emoticons)) {
    if (!emoticonGroups[emoticon.set]) {
      emoticonGroups[emoticon.set] = {items: [], info: state.emoticon_sets[emoticon.set]}
    }
    emoticonGroups[emoticon.set].items.push(emoticon)
  }

  // Generate the HTML for the emoticon groups.
  const buffer = []
  for (const group of Object.values(emoticonGroups)) {
    buffer.push('<div class="blue_component_light blue_component_section post_emoticon_sets">')
    if (group.info.title && group.info.display) {
      buffer.push(`<div class="emoticon_header">${group.info.title}:</div>`)
    }
    buffer.push('<div class="iconset">')
    for (const emoticon of group.items) {
      buffer.push(`<a href="#${emoticon.name}" data-emoticon="${emoticon.name}">`)
      buffer.push(`<img src="${context.theme_url}${state.emoticon_path}/${emoticon.set}/${emoticon.file}" name="post_icon" class="emoticon pixel" alt="${emoticon.name}" title="${emoticon.name}" width="${emoticon.size[0]}" height="${emoticon.size[1]}">`)
      buffer.push(`</a>`)
    }
    buffer.push('</div>')
    buffer.push('</div>')
  }

  return `
    <!doctype html>
    <html>
      <head>
        <meta charset="utf-8">
        <title>${txt.title}</title>
        <link rel="stylesheet" type="text/css" href="${context.theme_url}/css/index.css">
      </head>
      <body>
        <div id="wrapper" class="pop_up_window">
          <div class="blue_component">
            <div class="blue_component_header">
              <h3 class="blue_component_title">Emoticon picker</h3>
            </div>
            ${buffer.join('\n')}
            <div class="blue_component_medium blue_component_section">
              <button id="emoticon_window_close">
                Close window
              </button>
            </div>
          </div>
        </div>
      </body>
    </html>
  `
}

function openEmoticonsPopup() {
  const width = 600
  const height = 480
  // TODO
  	// Focus the window if it's already opened.
	if (state.emoticonPopupWindow !== null && 'closed' in state.emoticonPopupWindow && !state.emoticonPopupWindow.closed)
	{
		state.emoticonPopupWindow.focus();
		return;
	}

	// Get the smiley HTML.
	// Open the popup.
	state.emoticonPopupWindow = window.open('', '_addMoreSmileysPopup', `toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=${width},height=${height},resizable=yes`);

	// Paste the template in the popup.
  const content = generateEmoticonsPopup()
	state.emoticonPopupWindow.document.open('text/html', 'replace');
	state.emoticonPopupWindow.document.write(content);
	state.emoticonPopupWindow.document.close();

	// Initialize the smileys that are in the popup window.
	//state.initSmileys('popup', state.emoticonPopupWindow.document);

	// Add a function to the close window button.
  const closer = state.emoticonPopupWindow.document.querySelector('#emoticon_window_close')
  closer.addEventListener('click', ev => {
    ev.preventDefault()
		state.emoticonPopupWindow.close()
  })
}

/**
 * Adds functionality to the main post <textarea> field.
 */
function decorateMessageField() {
  const message = document.querySelector('#message')
  const selection = document.querySelector('#emoticons_selection')
  const button = document.querySelector('#button_emoticons')

  state.messageNode = message

  button.addEventListener('click', ev => {
    if (ev.explicitOriginalTarget !== button) {
      return true
    }
    ev.preventDefault()
    openEmoticonsPopup()
  })

  selection.addEventListener('click', ev => {
    let target = ev.originalTarget

    // Ignore only the 'show more' button.
    if (target === button) {
      return
    }

    // Ensure we're targeting the anchor.
    if (target.tagName === 'IMG') {
      target = target.parentNode
    }

    const emoticon = target.getAttribute('data-emoticon')
    if (emoticon === null) {
      // If there's no data-emoticon field, the user clicked inside
      // the #emoticons_selection div but not on an emoticon.
      return
    }
    return insertEmoticonToMessage(emoticon)
  });
}

/**
 * Adds functionality to the posticons radio buttons.
 */
function decoratePostIcons() {
  const selection = document.querySelector('#posticons_selection')
  const prefix = document.querySelector('#posticon_prefix')

  const image = prefix.querySelector('img')
  const baseURL = prefix.getAttribute('data-prefix')

  selection.addEventListener('change', ev => {
    const radio = ev.originalTarget
    const base = radio.getAttribute('data-basename')
    const url = `${baseURL}/${base}`
    image.setAttribute('src', url)
  });
}

/**
 * Initializes the theme code.
 */
function themeInit() {
  for (const [key, value] of Object.entries(window['gwThemeData'])) {
    state[key] = value
  }
  for (const [key, value] of Object.entries(window['gwContext'])) {
    context[key] = value
  }
}
