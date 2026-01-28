import * as THREE from 'three/build/three.module.js';
import { TextGeometry } from 'three/examples/jsm/geometries/TextGeometry.js';

const threeWithText = {
    ...THREE,
    TextGeometry,
};

export { TextGeometry };
export default threeWithText;
export * from 'three/build/three.module.js';

