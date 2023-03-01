/**
 * Javascript Controller for the a video bar button
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2023 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
export function VideoBarButton(node, defaultValue, callback)
{
    let btn = this;
    btn.node = node;
    btn.value = defaultValue;
    btn.callback = callback;
    btn.enabled = defaultValue === true;

    btn.init = function()
    {
        if (btn.enabled)
        {
            btn.attach();
        }
        else
        {
            btn.detatch();
        }
    };

    btn.attach = function() {
        if (this.node)
        {
            this.node.addEventListener('click', this.callback);
            this.node.classList.remove('d-none');
        }
    };

    btn.detatch = function()
    {
        if (this.node)
        {
            this.node.removeEventListener('click', this.callback);
            this.node.classList.add('d-none');
        }
    };
    btn.destruct = function()
    {
        // remove event handlers and cleanup memory.
        btn.detatch();
        btn.node = null;
        btn.callback = null;
    }
    btn.toggle = function()
    {
        btn.enabled = !btn.enabled;
        if (btn.enabled)
        {
            this.attach();
        }
        else
        {
            this.detatch();
        }
    };
    btn.init();
    return btn;
}