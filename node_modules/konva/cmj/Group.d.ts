import { Container } from './Container';
import { Node } from './Node';
import { Shape } from './Shape';
export declare class Group extends Container<Group | Shape> {
    _validateAdd(child: Node): void;
}
