import { Util } from './Util.js';
import { Container } from './Container.js';
import { _registerNode } from './Global.js';
export class Group extends Container {
    _validateAdd(child) {
        var type = child.getType();
        if (type !== 'Group' && type !== 'Shape') {
            Util.throw('You may only add groups and shapes to groups.');
        }
    }
}
Group.prototype.nodeType = 'Group';
_registerNode(Group);
