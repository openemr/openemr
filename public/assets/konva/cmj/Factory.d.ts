export declare const Factory: {
    addGetterSetter(constructor: any, attr: any, def?: any, validator?: any, after?: any): void;
    addGetter(constructor: any, attr: any, def?: any): void;
    addSetter(constructor: any, attr: any, validator?: any, after?: any): void;
    overWriteSetter(constructor: any, attr: any, validator?: any, after?: any): void;
    addComponentsGetterSetter(constructor: any, attr: any, components: any, validator?: any, after?: any): void;
    addOverloadedGetterSetter(constructor: any, attr: any): void;
    addDeprecatedGetterSetter(constructor: any, attr: any, def: any, validator: any): void;
    backCompat(constructor: any, methods: any): void;
    afterSetFilter(): void;
};
