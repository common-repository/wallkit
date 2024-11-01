// Before Wallkit run init
(function () {
    let userHideClass = document.querySelector("body");
    if(!userHideClass.classList.contains('wkwp-user-hide'))
    userHideClass.classList.add('wkwp-user-hide');
})();

(function () {
    window.wkwpCheckAccessPromise   = null;
    var wkContentBody               = '';
    var wkPaywallBlock              = '';

    window.addEventListener('DOMContentLoaded', (event) => {
        wkwpDebug('DOMContentLoaded');
        if(window.runInitWKPromise) {
            wkwpDebug('window.runInitWKPromise', window.runInitWKPromise);
            window.runInitWKPromise.then((response) => {
                wkwpDebug('window.runInitWKPromise.response', response);
                if(response.init === true) {
                    runInitWkProcess();
                }
            }).catch(error => {
                console.log('WKWP ERROR: runInitWKPromise', error);
            });
        } else {
            wkwpDebug('else runInitWkProcess');
            runInitWkProcess();
        }
    });

    // Init Wallkit and subscribe on user events
    function runInitWkProcess() {
        let wkSettings = window["wallkitSettings"] || {};
        let inlineModalsContainer = document.querySelector(wkSettings.config.inline_modals_selector);
        wkwpDebug('F=>runInitWkProcess.wkSettings', wkSettings);
        if(typeof wkSettings.integration === 'undefined' ) {
            return;
        }

        if (wkSettings.config.inline_modals_selector
            && inlineModalsContainer
            && inlineModalsContainer.dataset.modal
        ) {
            wkwpDebug('F=>runInitWkProcess.inlineModalsContainer',
                '\ninline_modals_selector', wkSettings.config.inline_modals_selector,
                '\ninlineModalsContainer', inlineModalsContainer,
                '\ninlineModalsContainer.dataset.modal', inlineModalsContainer.dataset.modal,
            );
            window.location.hash = 'WkModal(' + inlineModalsContainer.dataset.modal + ')';
        }

        window.wk = window.wk || [];
        window.wk.push(['ready', (params) => {
            wkwpDebug('F=>runInitWkProcess.window.wk ready callback', window.wk, params);
            wkwpDebug('F=>runInitWkProcess.window.wk.authentication', window.wk.authentication,
                '\nwindow.wk.authentication.isAuthenticated()=>', window.wk.authentication.isAuthenticated()
            );
            wkShowUserStatus();
            wkCheckPostAccess(false);

            //Wallkit default auth methods
            window.wk.on("wk-event-auth", function () {
                wkwpDebug('E=>wk.events.subscribe=>wk-event-auth');
                // Without reloading page
                wkShowUserStatus();
                wkCheckPostAccess();
            });

            window.wk.on("wk-event-registration", function () {
                wkwpDebug('E=>wk.events.subscribe=>wk-event-registration');
                // Without reloading page
                wkShowUserStatus();
                wkCheckPostAccess();
            });
            // End Wallkit default auth methods

            // Firebase auth method
            window.wk.on("success-auth", function ({register}) {
                wkwpDebug('E=>wk.events.subscribe=>success-auth', register);
                if(wkSettings.config.wk_auth_migrated_users === true) {
                    let userWithoutSessionInfo = document.querySelector('.wk-user-update-info');
                    wkwpDebug('E=>wk.events.subscribe=>success-auth',
                        '\nwk_auth_migrated_users=>', wkSettings.config.wk_auth_migrated_users,
                        '\nuserWithoutSessionInfo=>', userWithoutSessionInfo);
                    if(userWithoutSessionInfo) {
                        hideUserWithoutSessionText();
                    }
                }

                // If modal embed inline, after sign-in open account-settings modal
                if (wkSettings.config.inline_modals_selector && inlineModalsContainer) {
                    window.wk.modal(wkSettings.config.wk_modal_after_sign_in);
                }

                // Without reloading page
                wkShowUserStatus();
                wkCheckPostAccess();
            });

            window.wk.on("logout", function () {
                wkwpDebug('E=>wk.events.subscribe=>logout', wkSettings.config.reload_on_logout);
                if(wkSettings.config.reload_on_logout !== true) {
                    // Without reloading page
                    wkShowUserStatus();
                    wkCheckPostAccess();
                    window.wk.popup.hide();
                } else {
                    location.reload();
                }
            });

            window.wk.on("wk-event-transaction", function () {
                wkwpDebug('E=>wk.events.subscribe=>wk-event-transaction');
                // location.reload();
                // Without reloading page
                wkCheckPostAccess();
            });

            if(wkSettings.config.wk_auth_migrated_users === true) {
                window.wk.on('pre-sign-in', async (data) => {
                    wkwpDebug('E=>wk.events.subscribe=>pre-sign-in', data);
                    let signInSubmitSpinner =  document.querySelector("#auth-modal-wallkit-modal-spinner");
                    signInSubmitSpinner.style.display = 'flex';
                    let is_exist_sessions = true;
                    let is_password_reset = false;
                    let user_has_password = false;
                    await Wallkit.checkUserActivity(data.email).then((data) => {
                        wkwpDebug('E=>wk.events.subscribe=>pre-sign-in=>Wallkit.checkUserActivity.data', data);
                            if (typeof data !== 'undefined') {
                                if (typeof data.is_exist_sessions !== 'undefined'
                                    && typeof data.is_exist_sessions === "boolean") {
                                    is_exist_sessions = data.is_exist_sessions;
                                }

                                if (typeof data.has_user_resource_relationship_password !== 'undefined'
                                    && typeof data.has_user_resource_relationship_password === "boolean") {
                                    user_has_password = data.has_user_resource_relationship_password;
                                }
                            }
                        }, (error) => {
                            wkwpDebug('E=>wk.events.subscribe=>pre-sign-in=>Wallkit.checkUserActivity.error', error);
                        }
                    );

                    if (!is_exist_sessions && !user_has_password) {
                        wkwpDebug('E=>wk.events.subscribe=>pre-sign-in=>password-reset',
                            '\nis_exist_sessions=>', is_exist_sessions,
                            '\nuser_has_password=>', user_has_password);

                        await window.wk.sdk.methods.client.post({
                            path: '/firebase/password-reset',
                            data: {
                                email: data.email
                            }
                        }).then((responce) => {
                            wkwpDebug('E=>wk.events.subscribe=>pre-sign-in=>password-reset.responce', responce);
                            is_password_reset = true;
                        }, (error) => {
                            wkwpDebug('E=>wk.events.subscribe=>pre-sign-in=>password-reset.error', error);
                        });
                    }

                    if (is_password_reset) {
                        let authForm = document.querySelector('#wk-email-auth-form');
                        wkwpDebug('E=>wk.events.subscribe=>pre-sign-in',
                            '\nis_password_reset=>', is_password_reset,
                            '\nauthForm=>', authForm);

                        let errorMessage = authForm.querySelector('.wk-form').querySelector('.wk-form__error');
                        if (errorMessage) {
                            errorMessage.style.display = 'none';
                        }

                        let userWithoutSessionInfo = document.createElement('div');
                        let infoBlock = '<div style="padding: 24px 24px 0 24px;">' + wkSettings.config.wk_auth_migrated_users_text + '</div>';
                        userWithoutSessionInfo.classList.add('wk-user-update-info');
                        userWithoutSessionInfo.insertAdjacentHTML('beforeend', infoBlock);
                        authForm.parentNode.insertBefore(userWithoutSessionInfo, authForm);
                        authForm.style.display = 'none';

                        document.addEventListener('click', hideUserWithoutSessionInfo);
                    }

                    signInSubmitSpinner.style.display = 'none';
                    return true;
                });
            }
        }]);

        if(wkSettings.config.inline_modals_selector && inlineModalsContainer) {
            wkSettings.integration.ui = {
                type: 'inline',
                selector: wkSettings.config.inline_modals_selector,
            };
        }

        if(typeof WallkitIntegration === 'function') {
            window.wk = new WallkitIntegration(wkSettings.integration);
        } else {
            console.log('WKWP ERROR: WallkitIntegration function does not exist');
        }
    }

    // Handle auth user or guest and display relevant copies
    async function wkShowUserStatus() {
        let wkSettings = window["wallkitSettings"] || {};
        let wkTranslations = window["wallkitTranslations"] || {};
        const accountStatusSpanEls = document.querySelectorAll(".wkwp-user-my-account-button");
        wkwpDebug('F=>wkShowUserStatus',
            '\nwkSettings=>', wkSettings,
            '\naccountStatusSpanEls=>', wkTranslations,
            '\naccountStatusSpanEls=>', accountStatusSpanEls);

        if( !accountStatusSpanEls.length ) {
            return;
        }
        const userAccountBlock = document.querySelectorAll(".wkwp-login-block");

        const imgDefaultSrc = 'https://www.gravatar.com/avatar/?d=mp';
        const accountStatusImgEls = document.querySelectorAll(".wkwp-user-my-account-img");
        const accountSiteLogo = document.querySelectorAll(".wkwp-site-logo");
        let wkCallClass = wkSettings.integration.call.classForHandleClick || 'wk-call';
        const userHideClass = document.querySelector("body.wkwp-user-hide");
        wkwpDebug('F=>wkShowUserStatus.args',
            '\nuserAccountBlock', userAccountBlock,
            '\nimgDefaultSrc', imgDefaultSrc,
            '\naccountStatusImgEls', accountStatusImgEls,
            '\naccountSiteLogo', accountSiteLogo,
            '\nwkCallClass', wkCallClass,
            '\nwindow.wk.authentication.isAuthenticated()=>', window.wk.authentication.isAuthenticated());

        window.wkwpGetUser = new Promise((resolve, reject) => {
            if(!window.wk.authentication.isAuthenticated()) {
                wkwpDebug('F=>wkShowUserStatus.wkwpGetUser.wk.authentication.isAuthenticated()', window.wk.authentication.isAuthenticated());
                reject();
                return;
            }

            window.wk.sdk.methods.client.get({
                path: '/user'
            })
                .then((response) => {
                    wkwpDebug('F=>wkShowUserStatus.wkwpGetUser.wk.sdk.methods.client(/user)', response);
                    if (response.id > 0) {
                        window.wk.sdk.methods.user = response;
                    }

                    resolve(response);
                }, (error) => {
                    console.log('F=>wkShowUserStatus.wkwpGetUser ERROR', error);
                    reject(error);
                });
        });

        await window.wkwpGetUser.then((response) => {
            wkwpDebug('F=>wkShowUserStatus.wkwpGetUser.then.response', response);
            /**
             * Dispatch event on get user is completed
             *
             * @return object response of wk.sdk.methods.client.get -> /user.
             */
            wkwpDebug('F=>wkShowUserStatus.dispatchEvent.wkwpGetUserCompleted');
            window.dispatchEvent(new CustomEvent('wkwpGetUserCompleted', {
                detail: response
            }));

            if (response.id > 0) {
                if( accountStatusImgEls.length > 0 ) {
                    accountStatusImgEls.forEach(element => {
                        element.src = window.wk.sdk.methods.user.photos.image_100;
                        element.classList.remove(wkCallClass, 'wk–sign-in' );
                        element.classList.add(wkCallClass, 'wk–account-settings');
                        element.style = "display:block;";
                    });
                }

                if( accountStatusSpanEls.length > 0 ) {
                    accountStatusSpanEls.forEach(element => {
                        if(element.tagName === 'LI') {
                            element = element.querySelector('a');
                        }
                        element.innerHTML = wkSettings.titles.myAccountButton || wkTranslations.my_account || 'My&nbsp;Account'
                        element.classList.remove(wkCallClass, 'wk–sign-in' );
                        element.classList.add(wkCallClass, 'wk–account-settings');
                    });
                }

                if( accountSiteLogo.length > 0 ) {
                    accountSiteLogo.forEach(element => {
                        element.style = "display:none;";
                    });
                }
            }

            if( userAccountBlock.length > 0 ) {
                userAccountBlock.forEach(element => {
                    element.style = "display:block;";
                });
            }

            if(userHideClass) {
                userHideClass.classList.remove('wkwp-user-hide');
            }
        }).catch(error => {
            wkwpDebug('F=>wkShowUserStatus.wkwpGetUser.catch.error', error);
            /**
             * Dispatch event on get user is completed with error
             *
             * @return object response of wk.sdk.methods.client.get -> /user.
             */
            wkwpDebug('F=>wkShowUserStatus.dispatchEvent.wkwpGetUserCompleted.error');
            window.dispatchEvent(new CustomEvent('wkwpGetUserCompleted', {
                detail: false
            }));

            if( accountStatusImgEls.length > 0 ) {
                accountStatusImgEls.forEach(element => {
                    element.src = imgDefaultSrc;
                    element.classList.remove(wkCallClass,'wk–account-settings');
                    element.classList.add(wkCallClass,'wk–sign-in');
                    element.style = "display:none;";
                });
            }

            if( accountStatusSpanEls.length > 0 ) {
                accountStatusSpanEls.forEach(element => {
                    if(element.tagName === 'LI') {
                        element = element.querySelector('a');
                    }
                    element.classList.remove(wkCallClass,'wk–account-settings');
                    element.classList.add(wkCallClass,'wk–sign-in');
                    element.innerHTML = wkSettings.titles.signInButton || wkTranslations.sign_in || 'Sign&nbsp;in';
                });
            }

            if( accountSiteLogo.length > 0 ) {
                accountSiteLogo.forEach(element => {
                    element.style = "display:block;";
                });
            }

            if( userAccountBlock.length > 0 ) {
                userAccountBlock.forEach(element => {
                    element.style = "display:block;";
                });
            }

            if(userHideClass) {
                userHideClass.classList.remove('wkwp-user-hide');
            }
        });

        updateButtonsIfUrl();
    }

    function updateButtonsIfUrl() {
        const wkSettings = window["wallkitSettings"] || {};
        const wkCallClass = wkSettings.integration.call.classForHandleClick || 'wk-call';
        if(!wkSettings.config.wk_my_account_page_url) return;

        const signInEls          = document.querySelectorAll(".wk–sign-in");
        if(signInEls.length) {
            findAndReplaceHrefInElement(
                signInEls,
                wkSettings.config.wk_my_account_page_url,
                [wkCallClass, 'wk–sign-in']
            );
        }
        const accountSettingsEls = document.querySelectorAll(".wk–account-settings");
        if(accountSettingsEls.length) {
            findAndReplaceHrefInElement(
                accountSettingsEls,
                wkSettings.config.wk_my_account_page_url,
                [wkCallClass, 'wk–account-settings']
            );
        }
    }

    function findAndReplaceHrefInElement(rootEl, href, classes = []) {
        if(!rootEl || !href) return;
        rootEl.forEach((mainElement) => {
            let link = mainElement;
            if(classes.length) {
                mainElement.classList.remove(...classes);
            }
            if(mainElement.tagName !== 'A') {
                link = mainElement.querySelector('a');
            }
            if(link) {
                link.setAttribute('href', href);
            }
        });
    }

    // Based on settings send check post access request to Wallkit
    function wkCheckPostAccess(newCheckAccessPromise = true) {
        let wkSettings = window["wallkitSettings"] || {};
        wkwpDebug('F=>wkCheckPostAccess',
            '\nwkSettings', wkSettings,
            '\nnewCheckAccessPromise', newCheckAccessPromise,
            '\nwindow.wkwpCheckAccessPromise', window.wkwpCheckAccessPromise,
            '\nwindow.wallkitPostData', window.wallkitPostData,
            '\n.wkwp-paywall', document.querySelector(".wkwp-paywall")
        );

        if(newCheckAccessPromise === true) {
            window.wkwpCheckAccessPromise = null
        }

        if ( !window.wkwpCheckAccessPromise
            && typeof window.wallkitPostData.config !== "undefined"
            && typeof window.wallkitPostData.config.check_post !== "undefined"
            && window.wallkitPostData.config.check_post !== false
            && typeof window.wallkitPostData.data !== "undefined"
            && typeof window.wallkitPostData.data.id !== "undefined"
            && window.wallkitPostData.data.id !== ''
            && (document.querySelector(".wkwp-paywall")
                || ( wkSettings.config.content_class_selector && document.querySelector(`.${wkSettings.config.content_class_selector}`) )
                || ( wkSettings.config.custom_content_selector && document.querySelector(`${wkSettings.config.custom_content_selector}`) )
            )
        ) {
            const postInfo = window.wallkitPostData.data;
            const wkPost = new window.wk.content(postInfo);
            wkwpDebug('F=>wkCheckPostAccess.checkAccess',
                '\npostInfo', postInfo,
                '\nwkPost', wkPost);

            window.wkwpCheckAccessPromise = new Promise((resolve, reject) => {
                //If this is backed paywalled skip check post access on frontend, check access on backend.
                if(window.wallkitPostData.config.wk_paywall_display_type === 3) {
                    return resolve(get_content_part());
                }
                wkPost.checkAccess().then((response) => {
                    wkwpDebug('F=>wkCheckPostAccess.wkPost.checkAccess', response);
                    return resolve(response);
                }).catch((error) => {
                    console.log('WKWP wkPost ERROR', error);
                    return reject(error);
                });
            });
        }

        if(window.wkwpCheckAccessPromise) {
            window.wkwpCheckAccessPromise.then((response) => {
                window.removeEventListener('unlockContent', handleUnlockContentEvent);
                wkwpDebug('F=>wkCheckPostAccess.wkwpCheckAccessPromise', response);
                switch (window.wallkitPostData.config.wk_paywall_display_type) {
                    case 1: checkAccessHandlingFrontend(response); break;
                    case 3: checkAccessHandlingBackend(response); break;
                    default: checkAccessHandlingCSS(response);
                }

                /**
                 * Add event listener allow to direct unlock content.
                 */
                window.addEventListener('unlockContent', handleUnlockContentEvent);

                /**
                 * Dispatch event on locked content is completed
                 *
                 * @return object response of checkAccess() function.
                 */
                wkwpDebug('F=>wkCheckPostAccess.dispatchEvent.wkwpContentLocked');
                window.dispatchEvent(new CustomEvent('wkwpContentLocked', {
                    detail: response
                }));

            }).catch(error => {
                console.log('WKWP ERROR: wkwpCheckAccessPromise', error);
            });
        }
    }

    function handleUnlockContentEvent(e) {
        wkwpDebug('F=>handleUnlockContentEvent.addEventListener.unlockContent', e);
        switch (window.wallkitPostData.config.wk_paywall_display_type) {
            case 1: checkAccessHandlingFrontend({allowed: true}); break;
            default: checkAccessHandlingCSS({allowed: true});
        }
    }

    // Change view for user based on access. Partial process on backend.
    // Hard locking content
    function checkAccessHandlingCSS(response) {
        let wkSettings = window["wallkitSettings"] || {};
        const postContentWrapper    = document.querySelector(".wkwp-paywall");
        const postContentBody       = document.querySelector(".wkwp-paywall .wkwp-content-inner");
        let postPaywallBlock        = document.querySelector(".wkwp-paywall .wkwp-paywall-block");
        wkwpDebug('F=>checkAccessHandlingCSS',
            '\nresponse', response,
            '\nwindow.wallkitPostData', window.wallkitPostData,
            '\npostContentWrapper', postContentWrapper,
            '\npostContentBody', postContentBody,
            '\npostPaywallBlock', postPaywallBlock,
            '\nwkContentBody', wkContentBody,
            '\nwkPaywallBlock', wkPaywallBlock);

        if(postContentBody && !wkContentBody) {
            wkContentBody = postContentBody.innerHTML;
        }

        if (!response.allowed) {
            if (postContentBody) {
                if(wkSettings.config.skip_lorem === false) {
                    postContentBody.innerHTML = build_lorem_content(postContentBody);
                }

                if (!window.wallkitPostData.config.show_blur) {
                    postContentBody.style = "display:none;";
                } else {
                    postContentBody.classList.add('wkwp-content-blured');
                }
            }

            if (!postPaywallBlock && wkSettings.config.paywall.content) {
                postPaywallBlock = document.createElement('div');
                postPaywallBlock.classList.add('wkwp-paywall-block');
                postPaywallBlock.insertAdjacentHTML('beforeend', wkSettings.config.paywall.content);
                postContentBody.parentNode.insertBefore(postPaywallBlock, postContentBody);
            }

            paywallDisplayLoginLink(postPaywallBlock);

            postContentWrapper.style = "display:block;";
        } else {
            if (postPaywallBlock) {
                postPaywallBlock.remove();
            }

            if (postContentBody) {
                if (wkContentBody && wkSettings.config.skip_lorem === false) {
                    postContentBody.innerHTML = wkContentBody;
                }
                postContentBody.classList.remove('wkwp-content-blured');
                postContentBody.style = "display:block;";
            }
            postContentWrapper.style = "display:block;";
        }
    }

    // Change view for user based on access. Partial process on backend.
    // Hard locking content
    function checkAccessHandlingBackend(response) {
        let wkSettings = window["wallkitSettings"] || {};
        const postContentWrapper    = document.querySelector(".wkwp-paywall");
        const postContentBody       = document.querySelector(".wkwp-paywall .wkwp-content-inner");
        let postPaywallBlock        = document.querySelector(".wkwp-paywall .wkwp-paywall-block");
        wkwpDebug('F=>checkAccessHandlingBackend',
            '\nresponse', response,
            '\nwindow.wallkitPostData', window.wallkitPostData,
            '\npostContentWrapper', postContentWrapper,
            '\npostContentBody', postContentBody,
            '\npostPaywallBlock', postPaywallBlock,
            '\nwkPaywallBlock', wkPaywallBlock);


        if (!response.allowed) {
            if (postContentBody) {
                const p_count = parseInt(postContentBody.dataset.paragraphs) || 0;
                postContentBody.innerHTML = get_lorem(p_count);

                if (!window.wallkitPostData.config.show_blur) {
                    postContentBody.style = "display:none;";
                } else {
                    postContentBody.classList.add('wkwp-content-blured');
                }
            }

            if (!postPaywallBlock && wkSettings.config.paywall.content) {
                postPaywallBlock = document.createElement('div');
                postPaywallBlock.classList.add('wkwp-paywall-block');
                postPaywallBlock.insertAdjacentHTML('beforeend', wkSettings.config.paywall.content);
                postContentBody.parentNode.insertBefore(postPaywallBlock, postContentBody);
            }

            paywallDisplayLoginLink(postPaywallBlock);

            postContentWrapper.style = "display:block;";
        } else {
            let contentData = {
                contentScripts: [],
                contentPart: response.wp_content_part
            };

            if(response.wp_content_part
                && wkSettings.config.parse_scripts) {
                contentData.contentScripts = parseScripts(response.wp_content_part);
                contentData.contentPart = removeScriptsFromString(response.wp_content_part);
            }
            wkwpDebug('F=>checkAccessHandlingBackend',
                '\ncontentPart', contentData.contentPart,
                '\ncontentScripts', contentData.contentScripts);

            if (postPaywallBlock) {
                postPaywallBlock.remove();
            }

            if (postContentBody) {
                postContentBody.innerHTML = contentData.contentPart || '';
                if(contentData.contentScripts) {
                    contentData.contentScripts.forEach(script => document.body.appendChild(script));
                }
                postContentBody.classList.remove('wkwp-content-blured');
                postContentBody.style = "display:block;";
            }

            postContentWrapper.style = "display:block;";
        }
    }

    async function get_content_part() {
        const post_id = window.wallkitPostData.data.id.split('_').slice(-1);
        const url_params = {
            post_id: post_id
        };
        let headers = {};
        if(Wallkit.getToken()) {
            headers['wk-token'] = Wallkit.getToken();
        }
        if(Wallkit.getFirebaseToken()) {
            headers['firebase-token'] = Wallkit.getFirebaseToken();
        }
        if(WallkitClient.session) {
            headers['wk-session'] = WallkitClient.session;
        }

        wkwpDebug('F=>get_content_part',
            '\npost_id', post_id,
            '\nurl_params', url_params,
            '\nheaders', headers,
            '\nwindow.wallkitSettings.config.parse_scripts', window.wallkitSettings.config.parse_scripts
        );

        const contentAccessResponce = await fetch("/wp-json/wallkit/v1/get-content-part?" + new URLSearchParams(url_params).toString(), {
            method: "GET",
            headers: headers,
        })
            .then(response => {
                if(response.status !== 200) {
                    return false;
                }
                return response.json();
            })
            .catch((error) => {
                console.log('WKWP ERROR: get_content_part->contentAccessResponce', error);
                return false;
            });


        wkwpDebug('F=>get_content_part',
            '\ncontentAccessResponce', contentAccessResponce
        );

        return contentAccessResponce;
    }

    function parseScripts(content) {
        // Find all script tags and execute them
        const newScriptsElements = [];
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = content;
        const scripts = tempDiv.getElementsByTagName('script');

        wkwpDebug('F=>parseScripts',
            '\ncontent', content,
            '\nscripts', scripts,
        );
        for (let i = 0; i < scripts.length; i++) {
            const script = scripts[i];
            const newScript = document.createElement('script');

            if (script.src) {
                // If it's an external script (with src attribute), copy the src
                newScript.src = script.src;
                newScript.async = false; // Ensure synchronous execution if needed
            } else {
                // If it's an inline script, copy the text content
                newScript.text = script.innerHTML;
            }

            // Append the new script tag to the document to execute it
            newScriptsElements.push(newScript);
        }

        wkwpDebug('F=>parseScripts',
            '\nnewScriptsElements', newScriptsElements
        );
        return newScriptsElements;
    }

    function removeScriptsFromString(content) {
        wkwpDebug('F=>removeScriptsFromString',
            '\ncontent', content
        );

        // Create a temporary DOM element
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = content;

        // Find all script elements and remove them
        const scripts = tempDiv.getElementsByTagName('script');
        while (scripts.length > 0) {
            scripts[0].parentNode.removeChild(scripts[0]);
        }

        wkwpDebug('F=>removeScriptsFromString',
            '\ntempDiv.innerHTML', tempDiv.innerHTML
        );
        // Return the HTML content without scripts
        return tempDiv.innerHTML;
    }

    // Change view for user based on access. Full process on frontend.
    // Better for iframes.
    function checkAccessHandlingFrontend(response) {
        let wkSettings = window["wallkitSettings"] || {};
        let wkwpPaywall = document.querySelector(".wkwp-paywall");
        let contentSelector = `.${wkSettings.config.content_class_selector}`;
        if( wkSettings.config.custom_content_selector ) {
            contentSelector = wkSettings.config.custom_content_selector;
        }
        let topElement = document.querySelector(`${contentSelector}`);
        let allElements = document.querySelectorAll(`${contentSelector} > *`);
        let paragraphs = document.querySelectorAll(`${contentSelector} > p`);
        wkwpDebug('F=>checkAccessHandlingFrontend',
            '\nwkPaywallBlock', response,
            '\nwkSettings', wkSettings,
            '\nwkwpPaywall', wkwpPaywall,
            '\ntopElement', topElement,
            '\nallElements', allElements,
            '\nparagraphs', paragraphs);

        if (!topElement) {
            return;
        }

        if (!response.allowed) {
            if(topElement.querySelectorAll('.wkwp-element').length) {
                paywallDisplayLoginLink(wkwpPaywall);
                return;
            }

            let showParagraphs = paragraphs.length > wkSettings.config.wk_free_paragraph &&  wkSettings.config.wk_free_paragraph >= 0 ? wkSettings.config.wk_free_paragraph : paragraphs.length;
            const lastVisibleParagraph = paragraphs[showParagraphs > 0 ? showParagraphs - 1 : 0];
            const lastVisibleParagraphIndex = showParagraphs === 0 ? 0 : Array.prototype.indexOf.call(topElement.children, lastVisibleParagraph) + 1;
            var itemClass = ['wkwp-element'];
            if(window.wallkitPostData.config.show_blur) {
                itemClass.push('wkwp-blur');
            } else {
                itemClass.push('wkwp-non-blur');
            }

            wkwpDebug('F=>checkAccessHandlingFrontend.!allowed',
                '\nshowParagraphs', showParagraphs,
                '\nlastVisibleParagraph', lastVisibleParagraph,
                '\nlastVisibleParagraphIndex', lastVisibleParagraphIndex,
                '\nitemClass', itemClass);
            for(let i = lastVisibleParagraphIndex; i < allElements.length; i++) {
                allElements[i].classList.add(...itemClass);
            }

            if (!wkwpPaywall) {
                wkwpPaywall = document.createElement('div');
                wkwpPaywall.classList.add('wkwp-paywall');
                wkwpPaywall.insertAdjacentHTML('beforeend', `<div class="wkwp-paywall-block">${wkSettings.config.paywall.content}</div>`);
            }

            if(showParagraphs === 0) {
                topElement.insertBefore(wkwpPaywall, lastVisibleParagraph);
            } else {
                topElement.insertBefore(wkwpPaywall, lastVisibleParagraph.nextSibling);
            }

            paywallDisplayLoginLink(wkwpPaywall);

            wkwpPaywall.style = "display:block;";
        }
        else {
            if (wkwpPaywall) {
                wkwpPaywall.remove();
            }

            allElements.forEach((e) => {
                if (e.classList.contains('wkwp-element')) {
                    e.classList.remove('wkwp-element', 'wkwp-blur', 'wkwp-non-blur');
                }
            });
        }
    }

    function paywallDisplayLoginLink(paywallNode) {
        wkwpDebug('F=>paywallDisplayLoginLink', paywallNode);
        if (paywallNode) {
            let paywallBlockLoginLink = paywallNode.querySelector('.wallkit-paywall-block__login_plans');
            if (paywallBlockLoginLink) {
                if (window.wk.authentication.isAuthenticated()) {
                    paywallBlockLoginLink.style = "display:none;";
                } else {
                    paywallBlockLoginLink.style = "display:block;";
                }
            }
        }
    }

    function hideUserWithoutSessionInfo(e) {
        wkwpDebug('F=>hideUserWithoutSessionInfo', e);
        switch(e.target.id) {
            case 'auth-signup-link':
            case 'auth-modal-close-btn':
            case 'auth-modal-wrapper':
            case 'auth-password-link':
                hideUserWithoutSessionText();
                break;
        }
        if(e.target.classList.contains('wk-form-button')) {
            hideUserWithoutSessionText();
        }
    }

    function hideUserWithoutSessionText() {
        let userWithoutSessionInfo = document.querySelector('.wk-user-update-info');
        let authForm = document.querySelector('#wk-email-auth-form');
        let authFormHeaderError = document.querySelector('#wk-email-auth-form .wk-form .wk-form-header .wk-form__error');
        wkwpDebug('F=>hideUserWithoutSessionText',
            '\nuserWithoutSessionInfo', userWithoutSessionInfo,
            '\nauthForm', authForm,
            '\nauthFormHeaderError', authFormHeaderError);

        document.removeEventListener('click', hideUserWithoutSessionInfo);
        userWithoutSessionInfo.remove();
        authForm.style.display = 'block';

        if(authFormHeaderError) {
            authFormHeaderError.style.display = '';
        }
    }

    // Replace hidden part of content by lorem
    function build_lorem_content(el) {
        if(el && el.querySelectorAll('p').length > 0 ) {
            let length = el.querySelectorAll('p').length;

            return get_lorem(length);
        }

        return '';
    }

    function get_lorem(p_count) {
        const loremText = [
            `Inventore molestiae accusantium fuga delectus. Sed exercitationem aut quis reiciendis nesciunt dolore et. Voluptatibus at suscipit eius ratione perspiciatis provident. Totam minima quia occaecati maxime mollitia.`,
            `Laborum occaecati sapiente nesciunt voluptatem. Voluptatibus asperiores optio ut. Pariatur perspiciatis voluptatem beatae commodi libero modi.`,
            `Aut et ipsum beatae tenetur sit. Necessitatibus harum ea et. Natus aut quas sit dolores odio ut. Ipsum sit corporis maxime voluptatum et. Et labore id rerum nobis quia voluptatibus veniam dolores.`,
            `Sed aut repudiandae alias. Sunt est ab dignissimos quasi recusandae labore. Amet vitae illo debitis beatae nesciunt dolor dignissimos. Voluptatum consequuntur error at omnis. Tenetur quaerat facere placeat enim doloribus.`,
            `Id tempora quo placeat dolore. Eos sunt sapiente et facere. Ex facere et voluptate praesentium. Modi mollitia at non eum rerum perferendis. Nobis blanditiis consequatur incidunt.`,
            `Sint voluptas nam sed. Eligendi beatae corporis omnis ipsum facilis dicta a repudiandae. Suscipit et eligendi eveniet ipsum veritatis aut. Aspernatur adipisci fugit deserunt eos hic ut omnis. Corporis et itaque dicta similique.`,
            `Fugit dolores vitae iste qui. Dignissimos quo molestiae cumque. Sequi illo non saepe facere aut aliquid consequuntur sunt. Amet non illo dolor molestiae nulla eligendi quae. Id aut et velit quos sit ratione earum rerum.`,
            `Soluta error neque dolor perspiciatis mollitia. Voluptatem corporis doloribus fugiat et. Unde et rerum magni. Dolores nisi laudantium laboriosam voluptatem.`,
            `Dolores in illo exercitationem est enim pariatur quam corporis. Non ipsa sequi explicabo. Placeat earum aspernatur quod et quia.`,
            `Et sed amet ipsa. Qui et corrupti eaque et at dicta nesciunt vero. Est quasi eius possimus repellat ea ut. Inventore vel et possimus officiis quo consectetur similique. Culpa ut voluptatem non pariatur illum autem sapiente. Et et quidem est dolorum ab.`,
            `Amet id ipsam deleniti minima aut laboriosam. Odio ut reiciendis delectus repudiandae dolorem quaerat. Alias asperiores eum molestiae libero assumenda non voluptatibus quo. Et esse laboriosam ab velit et. Eligendi sint assumenda et.`,
            `Soluta ex voluptas minima et magnam est. Deserunt error molestiae veritatis amet. Quia provident sint molestiae omnis optio sunt sint. Commodi praesentium est perferendis inventore aut atque dolor doloribus. Minima enim velit eaque qui sed non dignissimos debitis. Rerum placeat in qui reprehenderit blanditiis nemo sed.`,
            `Magni dolore enim asperiores quae asperiores. Et quia eligendi ad quo aut labore ut iste. Quia qui esse aperiam eos illum exercitationem minus quod.`,
            `Sed ut dolorum sunt. Tempora incidunt aspernatur doloremque voluptatem quidem voluptatem magni. Est voluptatum minus id. Totam repudiandae reiciendis et.`,
            `Sint natus tenetur qui earum recusandae id optio. Dolore voluptates et accusamus et tempora sint. Dolores reiciendis iusto et quos aut. Sequi et officiis ipsum distinctio. Expedita voluptatibus corporis odio blanditiis iusto.`,
            `Ipsum dolores ut ut. Quia et voluptates accusamus neque quidem exercitationem dignissimos. Libero velit nemo omnis dolores ea repudiandae commodi accusamus. Sint tempore aut officia iste odit odio. Quibusdam sed debitis officiis.`,
            `Voluptatem quo est eius occaecati voluptatem tempore. Iste voluptas animi a voluptatem. Debitis est dolore aut fuga sunt voluptatem itaque assumenda. Id magnam officiis sint recusandae dolorum. Architecto numquam dignissimos quam corporis hic. Sit rerum amet provident.`,
            `Sed eum reiciendis aspernatur ab cupiditate. Ut atque dolores rerum veritatis voluptatem quidem ex voluptatum. Perspiciatis tempore quia quia animi vel distinctio. Id officia odit iusto facilis aliquid sequi eaque. Magnam eaque laudantium et et exercitationem.`,
            `Quod et ut voluptatibus assumenda sed. Nesciunt ea sed asperiores veniam temporibus blanditiis possimus. Quisquam eos voluptas assumenda molestiae.`,
            `Labore ipsum vitae dolorem est sed repellendus. Animi qui sequi similique dolorem sed vel omnis. Rerum saepe id atque animi.`
        ];

        let tempText = [];
        for ( var i = 0; i < p_count; i++ ) {
            tempText.push( loremText[Math.floor(Math.random() * loremText.length)] );
        }
        tempText = tempText.map(item => `<p>${item}</p>`).join("");

        return tempText;
    }

    function wkwpDebug(msg = '', ...optArgs) {
        if(typeof window["wallkitSettings"].config !== 'undefined'
            && window["wallkitSettings"].config.debug) {
            console.log("WKWP DEBUG: ", msg, ...optArgs);
        }
    }

})();