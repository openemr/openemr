// Type definitions for DataTables
//
// Project: https://datatables.net
// Definitions by:
//   SpryMedia
//   Kiarash Ghiaseddin <https://github.com/Silver-Connection>
//   Omid Rad <https://github.com/omidkrad>
//   Armin Sander <https://github.com/pragmatrix>
//   Craig Boland <https://github.com/CNBoland>

/// <reference types="jquery" />

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Types
 */
// No publicly documented properties
type InternalSettings = Object;

export type DomSelector =
    string |
    Node |
    JQuery;

export type InstSelector =
    DomSelector |
    InternalSettings;

export type RowIdx = number;
export type RowSelector<T> =
    RowIdx |
    string |
    Node |
    JQuery |
    ((idx: RowIdx, data: T, node: Node | null) => boolean) |
    RowSelector<T>[];

export type ColumnIdx = number;
export type ColumnSelector =
    ColumnIdx |
    string |
    Node |
    JQuery |
    ((idx:ColumnIdx, data: any, node: Node) => boolean) |
    ColumnSelector[];

export type CellIdx = {
    row: number;
    column: number;
};
export type CellSelector =
    CellIdx |
    string |
    Node |
    JQuery |
    ((idx: CellIdx, data: any, node: Node | null) => boolean) |
    CellSelector[];


export type CellIdxWithVisible = {
    row: number;
    column: number;
    columnVisible: number;
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Main interfaces
 */

/**
 * DataTables class
 */
declare interface DataTables<T> extends DataTablesStatic {
    /**
     * Create a new DataTable
     * @param selector Selector to pick one or more `<table>` elements
     * @param opts Configuration settings
     */
    new <T=any>(
        selector: InstSelector,
        opts?: Config
    ): Api<T>

    /**
     * CommonJS factory. This only applies to CommonJS environments,
     * in all others it would throw an error.
     */
    (win?: Window, jQuery?: JQuery): DataTables<T>;
}

declare const DataTables: DataTables<any>;
export default DataTables;

// The `$().dataTable()` method adds a `.api()` method to the object
// to allow access to the DataTables API
interface JQueryDataTables extends JQuery {
    /**
     * Returns DataTables API instance
     * Usage:
     * $( selector ).dataTable().api();
     */
    api(): Api<any>;
}

// Extend the jQuery object with DataTables' construction methods
declare global {
    interface JQueryDataTableApi extends DataTablesStatic {
        <T = any>(opts?: Config): Api<T>;
    }

    interface JQueryDataTableJq extends DataTablesStatic {
        (opts?: Config): JQueryDataTables;
    }

    interface JQuery {
        /**
         * Create a new DataTable, returning a DataTables API instance.
         * @param opts Configuration settings
         */
        DataTable: JQueryDataTableApi;

        /**
         * Create a new DataTable, returning a jQuery object, extended
         * with an `api()` method which can be used to access the
         * DataTables API.
         * @param opts Configuration settings
         */
        dataTable: JQueryDataTableJq;
    }
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Options
 */

export interface Config {
    /**
     * Load data for the table's content from an Ajax source.
     */
    ajax?: string | AjaxSettings | FunctionAjax;

    /**
     * Feature control DataTables' smart column width handling.
     */
    autoWidth?: boolean;

    /**
     * Data to use as the display data for the table.
     */
    columns?: ConfigColumns[];

    /**
     * Assign a column definition to one or more columns.
     */
    columnDefs?: ConfigColumnDefs[];

    /**
     * Data to use as the display data for the table.
     */
    data?: any[];

    /**
     * Delay the loading of server-side data until second draw
     */
    deferLoading?: number | number[];
 
    /**
     * Feature control deferred rendering for additional speed of initialisation.
     */
    deferRender?: boolean;

    /**
     * Destroy any existing table matching the selector and replace with the new options.
     */
    destroy?: boolean;

    /**
     * Initial paging start point.
     */
    displayStart?: number;

    /**
     * Define the table control elements to appear on the page and in what order.
     */
    dom?: string;

    /**
     * Feature control table information display field.
     */
    info?: boolean;

    /**
     * Language configuration object
     */
    language?: ConfigLanguage;

    /**
     * Feature control the end user's ability to change the paging display length of the table.
     */
    lengthChange?: boolean;

    /**
     * Change the options in the page length select list.
     */
    lengthMenu?: Array<(number | string)> | Array<Array<(number | string)>>;

    /**
     * Control which cell the order event handler will be applied to in a column.
     */
    orderCellsTop?: boolean;

    /**
     * Highlight the columns being ordered in the table's body.
     */
    orderClasses?: boolean;

    /**
     * Initial order (sort) to apply to the table.
     */
    order?: Array<(number | string)> | Array<Array<(number | string)>>;

    /**
     * Ordering to always be applied to the table.
     */
    orderFixed?: Array<(number | string)> | Array<Array<(number | string)>> | object;

    /**
     * Feature control ordering (sorting) abilities in DataTables.
     */
    ordering?: boolean;

    /**
     * Multiple column ordering ability control.
     */
    orderMulti?: boolean;

    /**
     * Change the initial page length (number of rows per page).
     */
    pageLength?: number;

    /**
     * Enable or disable table pagination.
     */
    paging?: boolean;

    /**
     * Pagination button display options. Basic Types: numbers (1.10.8) simple, simple_numbers, full, full_numbers
     */
    pagingType?: string;

    /**
     * Feature control the processing indicator.
     */
    processing?: boolean;

    /**
     * Display component renderer types.
     */
    renderer?: string | ConfigRenderer;

    /**
     * Retrieve an existing DataTables instance.
     */
    retrieve?: boolean;

    /**
     * Data property name that DataTables will use to set <tr> element DOM IDs. Since: 1.10.8
     */
    rowId?: string;

    /**
     * Allow the table to reduce in height when a limited number of rows are shown.
     */
    scrollCollapse?: boolean;

    /**
     * Horizontal scrolling.
     */
    scrollX?: boolean;

    /**
     * Vertical scrolling. Since: 1.10 Exp: "200px"
     */
    scrollY?: string;

    /**
     * Set an initial filter in DataTables and / or filtering options.
     */
    search?: ConfigSearch | boolean;

    /**
     * Define an initial search for individual columns.
     */
    searchCols?: ConfigSearch[];

    /**
     * Set a throttle frequency for searching.
     */
    searchDelay?: number;

    /**
     * Feature control search (filtering) abilities
     */
    searching?: boolean;

    /**
     * Feature control DataTables' server-side processing mode.
     */
    serverSide?: boolean;

    /**
     * Saved state validity duration.
     */
    stateDuration?: number;

    /**
     * State saving - restore table state on page reload.
     */
    stateSave?: boolean;

    /**
     * Set the zebra stripe class names for the rows in the table.
     */
    stripeClasses?: string[];

    /**
     * Tab index control for keyboard navigation.
     */
    tabIndex?: number;

    /**
     * Callback for whenever a TR element is created for the table's body.
     */
    createdRow?: FunctionCreateRow;

    /**
     * Function that is called every time DataTables performs a draw.
     */
    drawCallback?: FunctionDrawCallback;

    /**
     * Footer display callback function.
     */
    footerCallback?: FunctionFooterCallback;

    /**
     * Number formatting callback function.
     */
    formatNumber?: FunctionFormatNumber;

    /**
     * Header display callback function.
     */
    headerCallback?: FunctionHeaderCallback;

    /**
     * Table summary information display callback.
     */
    infoCallback?: FunctionInfoCallback;

    /**
     * Initialisation complete callback.
     */
    initComplete?: FunctionInitComplete;

    /**
     * Pre-draw callback.
     */
    preDrawCallback?: FunctionPreDrawCallback;

    /**
     * Row draw callback..
     */
    rowCallback?: FunctionRowCallback;

    /**
     * Callback that defines where and how a saved state should be loaded.
     */
    stateLoadCallback?: FunctionStateLoadCallback;

    /**
     * State loaded callback.
     */
    stateLoaded?: FunctionStateLoaded;

    /**
     * State loaded - data manipulation callback.
     */
    stateLoadParams?: FunctionStateLoadParams;

    /**
     * Callback that defines how the table state is stored and where.
     */
    stateSaveCallback?: FunctionStateSaveCallback;

    /**
     * State save - data manipulation callback.
     */
    stateSaveParams?: FunctionStateSaveParams;
}


export interface ConfigLanguage {
    emptyTable?: string;
    info?: string;
    infoEmpty?: string;
    infoFiltered?: string;
    infoPostFix?: string;
    decimal?: string;
    thousands?: string;
    lengthMenu?: string;
    loadingRecords?: string;
    processing?: string;
    search?: string;
    searchPlaceholder?: string;
    zeroRecords?: string;
    paginate?: {
        first?: string;
        last?: string;
        next?: string;
        previous?: string;
    };
    aria?: {
        sortAscending?: string;
        sortDescending?: string;
        paginate?: {
            first?: string;
            last?: string;
            next?: string;
            previous?: string;
        };
    };
    url?: string;
}


export interface ConfigColumns {
    /**
     * Set the column's aria-label title. Since: 1.10.25
     */
    ariaTitle?: string;

    /**
     * Cell type to be created for a column. th/td
     */
    cellType?: string;

    /**
     * Class to assign to each cell in the column.
     */
    className?: string;

    /**
     * Add padding to the text content used when calculating the optimal with for a table.
     */
    contentPadding?: string;

    /**
     * Cell created callback to allow DOM manipulation.
     */
    createdCell?: FunctionColumnCreatedCell;

    /**
     * Class to assign to each cell in the column.
     */
    data?: number | string | ObjectColumnData | FunctionColumnData | null;

    /**
     * Set default, static, content for a column.
     */
    defaultContent?: string;

    /**
     * Set a descriptive name for a column.
     */
    name?: string;

    /**
     * Enable or disable ordering on this column.
     */
    orderable?: boolean;

    /**
     * Define multiple column ordering as the default order for a column.
     */
    orderData?: number | number[];

    /**
     * Live DOM sorting type assignment.
     */
    orderDataType?: string;

    /**
     * Ordering to always be applied to the table. Since 1.10
     *
     * Array type is prefix ordering only and is a two-element array:
     * 0: Column index to order upon.
     * 1: Direction so order to apply ("asc" for ascending order or "desc" for descending order).
     */
    orderFixed?: any[] | OrderFixed;

    /**
     * Order direction application sequence.
     */
    orderSequence?: string[];

    /**
     * Render (process) the data for use in the table.
     */
    render?: number | string | ObjectColumnData | FunctionColumnRender | ObjectColumnRender;

    /**
     * Enable or disable filtering on the data in this column.
     */
    searchable?: boolean;

    /**
     * Set the column title.
     */
    title?: string;

    /**
     * Set the column type - used for filtering and sorting string processing.
     */
    type?: string;

    /**
     * Enable or disable the display of this column.
     */
    visible?: boolean;

    /**
     * Column width assignment.
     */
    width?: string;
}

export type ConfigColumnDefs = ConfigColumnDefsMultiple | ConfigColumnDefsSingle;

export interface ConfigColumnDefsMultiple extends ConfigColumns {
    /**
     * Target column(s). Either this or `target` must be specified.
     */
    targets: string | number | Array<(number | string)>;
}

export interface ConfigColumnDefsSingle extends ConfigColumns {
    /**
     * Single column target. Either this or `targets` must be specified. Since: 1.12
     */
    target: string | number;
}

export interface ConfigRenderer {
    header?: string;
    pageButton?: string;
}

export interface ConfigSearch {
    /**
     * Control case-sensitive filtering option.
     */
    caseInsensitive?: boolean;

    /**
     * Enable / disable escaping of regular expression characters in the search term.
     */
    regex?: boolean;

    /**
     * Enable / disable DataTables' smart filtering.
     */
    smart?: boolean;

    /**
     * Set an initial filtering condition on the table.
     */
    search?: string;

    /**
     * Set a placeholder attribute for input type="text" tag elements. Since: 1.10.1
     */
    searchPlaceholder?: string;
}


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * API
 */

export interface Api<T> {
    /**
     * API should be array-like
     */
    [key: number]: any;

    /**
     * Returns DataTables API instance
     *
     * @param table Selector string for table
     */
    (selector: string | Node | Node[] | JQuery): Api<T>;

    /**
     * Get jquery object
     * 
     * @param selector jQuery selector to perform on the nodes inside the table's tbody tag.
     * @param modifier Option used to specify how the content's of the selected columns should be ordered, and if paging or filtering in the table should be taken into account. 
     * @returns JQuery object with the matched elements in it's results set
     */
    $(selector: string | Node | Node[] | JQuery, modifier?: ApiSelectorModifier): JQuery;

    /**
     * Ajax Methods
     */
    ajax: ApiAjax;

    /**
     * Get a boolean value to indicate if there are any entries in the API instance's result set (i.e. any data, selected rows, etc).
     * @returns true if there there is one or more items in the result set, false otherwise.
     */
    any(): boolean;

    /**
     * Cell (single) selector and methods
     */
    cell: ApiCell<T>;

    /**
     * Cells (multiple) selector and methods
     */
    cells: ApiCells<T>;

    /**
     * Clear the table of all data.
     * 
     * @returns DataTables Api instance.
     */
    clear(): Api<T>;

    /**
     * Column Methods / object
     */
    column: ApiColumn;

    /**
     * Columns Methods / object
     */
    columns: ApiColumns<T>;

    /**
     * Concatenate two or more API instances together
     *
     * @param a API instance to concatenate to the initial instance.
     * @param b Additional API instance(s) to concatenate to the initial instance.
     * @returns New API instance with the values from all passed in instances concatenated into its result set.
     */
    concat(a: object, ...b: object[]): Api<any>;

    /**
     * Get the number of entries in an API instance's result set, regardless of multi-table grouping (e.g. any data, selected rows, etc). Since: 1.10.8
     * 
     * @returns The number of items in the API instance's result set
     */
    count(): number;

    /**
     * Get the data for the whole table.
     * 
     * @returns DataTables Api instance with the data for each row in the result set
     */
    data(): Api<T>;

    /**
     * Destroy the DataTables in the current context.
     *
     * @param remove Completely remove the table from the DOM (true) or leave it in the DOM in its original plain un-enhanced HTML state (default, false).
     * @returns DataTables Api instance
     */
    destroy(remove?: boolean): Api<T>;

    /**
     * Redraw the DataTables in the current context, optionally updating ordering, searching and paging as required.
     *
     * @param paging This parameter is used to determine what kind of draw DataTables will perform.
     * @returns DataTables Api instance
     */
    draw(paging?: boolean | string): Api<T>;

    /**
     * Iterate over the contents of the API result set.
     *
     * @param fn Callback function which is called for each item in the API instance result set. The callback is called with three parameters
     * @returns Original API instance that was used. For chaining.
     */
    each(fn: ((value: any, index: number, dt: Api<any>) => void)): Api<any>;

    /**
     * Reduce an Api instance to a single context and result set.
     *
     * @param idx Index to select
     * @returns New DataTables API instance with the context and result set containing the table and data for the index specified, or null if no matching index was available.
     */
    eq(idx: number): Api<any>;

    /**
     * Iterate over the result set of an API instance and test each item, creating a new instance from those items which pass.
     *
     * @param fn Callback function which is called for each item in the API instance result set. The callback is called with three parameters.
     * @returns New API instance with the values from the result set which passed the test in the callback.
     */
    filter(fn: ((value: any, index: number, dt: Api<any>) => boolean)): Api<Array<any>>;

    /**
     * Flatten a 2D array structured API instance to a 1D array structure.
     * 
     * @returns New API instance with the 2D array values reduced to a 1D array.
     */
    flatten(): Api<Array<any>>;

    /** 
     * Look up a language token that was defined in the DataTables' language initialisation object.
     *
     * @param token The language token to lookup from the language object.
     * @param def The default value to use if the DataTables initialisation has not specified a value. This can be a string for simple cases, or an object for plurals.
     * @param numeric If handling numeric output, the number to be presented should be given in this parameter.
     *
     * @returns Resulting internationalised string.
     */
    i18n(token: string, def: object | string, numeric?: number): string;

    /**
     * Find the first instance of a value in the API instance's result set.
     *
     * @param value Value to find in the instance's result set.
     * @returns The index of the item in the result set, or -1 if not found.
     */
    indexOf(value: any): number;

    /**
     * Get the initialisation options used for the table. Since: DataTables 1.10.6
     * 
     * @returns Configuration object
     */
    init(): Config;

    /**
     * Iterate over a result set of table, row, column or cell indexes
     * 
     * @param type Iterator type
     * @param callback Callback function that is executed on each iteration. For the parameters passed to the function, please refer to the documentation above. As of this is executed in the scope of an API instance which has its context set to only the table in question.
     * @param returns Indicate if the callback function will return values or not. If set to true a new API instance will be returns with the return values from the callback function in its result set. If not set, or false the original instance will be returned for chaining, if no values are returned by the callback method.
     * @returns Original API instance if the callback returns no result (i.e. undefined) or a new API instance with the result set being the results from the callback, in order of execution.
     */
    iterator(type: 'table', callback: InteratorTable, returns: boolean): Api<any>;
    iterator(type: 'cell', callback: InteratorCell, returns: boolean): Api<any>;
    iterator(type: 'column-rows', callback: InteratorColumnRows, returns: boolean): Api<any>;
    iterator(type: 'column', callback: InteratorColumn, returns: boolean): Api<any>;
    iterator(type: 'columns', callback: InteratorColumns, returns: boolean): Api<any>;
    iterator(type: 'row', callback: InteratorRow, returns: boolean): Api<any>;
    iterator(type: 'rows', callback: InteratorRows, returns: boolean): Api<any>;

    /**
     * Iterate over a result set of table, row, column or cell indexes
     * 
     * @param flatten If true the result set of the returned API instance will be a 1D array (i.e. flattened into a single array). If false (or not specified) each result will be concatenated to the instance's result set. Note that this is only relevant if you are returning arrays from the callback.
     * @param type Iterator type
     * @param callback Callback function that is executed on each iteration. For the parameters passed to the function, please refer to the documentation above. As of this is executed in the scope of an API instance which has its context set to only the table in question.
     * @param returns Indicate if the callback function will return values or not. If set to true a new API instance will be returns with the return values from the callback function in its result set. If not set, or false the original instance will be returned for chaining, if no values are returned by the callback method.
     * @returns Original API instance if the callback returns no result (i.e. undefined) or a new API instance with the result set being the results from the callback, in order of execution.
     */
    iterator(flatten: boolean, type: 'table', callback: InteratorTable, returns: boolean): Api<any>;
    iterator(flatten: boolean, type: 'cell', callback: InteratorCell, returns: boolean): Api<any>;
    iterator(flatten: boolean, type: 'column-rows', callback: InteratorColumnRows, returns: boolean): Api<any>;
    iterator(flatten: boolean, type: 'column', callback: InteratorColumn, returns: boolean): Api<any>;
    iterator(flatten: boolean, type: 'columns', callback: InteratorColumns, returns: boolean): Api<any>;
    iterator(flatten: boolean, type: 'row', callback: InteratorRow, returns: boolean): Api<any>;
    iterator(flatten: boolean, type: 'rows', callback: InteratorRows, returns: boolean): Api<any>;

    /**
     * Join the elements in the result set into a string.
     *
     * @param separator The string that will be used to separate each element of the result set.
     * @returns Contents of the instance's result set joined together as a single string.
     */
    join(separator: string): string;

    /**
     * Find the last instance of a value in the API instance's result set.
     *
     * @param value Value to find in the instance's result set.
     * @returns The index of the item in the result set, or -1 if not found.
     */
    lastIndexOf(value: any): number;

    /**
     * Number of elements in an API instance's result set.
     */
    length: number;

    /**
     * Iterate over the result set of an API instance, creating a new API instance from the values returned by the callback.
     *
     * @param fn Callback function which is called for each item in the API instance result set. The callback is called with three parameters.
     * @returns New API instance with the values in the result set as those returned by the callback.
     */
    map(fn: ((value: any, index: number, dt: Api<any>) => any)): Api<any>;

    /**
     * Remove event listeners that have previously been added with on().
     *
     * @param event Event name to remove.
     * @param callback Specific callback function to remove if you want to unbind a single event listener.
     * @returns DataTables Api instance
     */
    off(event: string, callback?: ((this: HTMLElement, e: Event, ...args: any[]) => void)): Api<T>;

    /**
     * Remove event handlers from selected elements
     *
     * @param event Event name to remove.
     * @param selector Element selector
     * @param callback Specific callback function to remove if you want to unbind a single event listener.
     * @returns DataTables Api instance
     */
    off(event: string, selector: string, callback?: ((this: HTMLElement, e: Event, ...args: any[]) => void)): Api<T>;

    /**
     * Table events listener.
     *
     * @param event Event to listen for.
     * @param callback Specific callback function to remove if you want to unbind a single event listener.
     * @returns DataTables Api instance
     */
    on(event: string, callback: ((this: HTMLElement, e: Event, ...args: any[]) => void)): Api<T>;

    /**
     * Listen for events from selected elements
     *
     * @param event Event to listen for.
     * @param selector Element selector
     * @param callback Event handler.
     * @returns DataTables Api instance
     */
    on(event: string, selector: string, callback: ((this: HTMLElement, e: Event, ...args: any[]) => void)): Api<T>;

    /**
     * Listen for a table event once and then remove the listener.
     *
     * @param event Event to listen for.
     * @param callback Specific callback function to remove if you want to unbind a single event listener.
     * Listen for events from tables and fire a callback when they occur
     * @returns DataTables Api instance
     */
    one(event: string, callback: ((this: HTMLElement, e: Event, ...args: any[]) => void)): Api<T>;

    /**
     * Listen for events from a selected element and trigger only once then remove the listener.
     *
     * @param event Event to listen for.
     * @param selector Element selector
     * @param callback Event handler.
     * @returns DataTables Api instance
     */
    one(event: string, selector: string, callback: ((this: HTMLElement, e: Event, ...args: any[]) => void)): Api<T>;

    /**
     * Page Methods / object
     */
    page: ApiPage;

    /**
     * Iterate over the result set of an API instance, creating a new API instance from the values retrieved from the original elements.
     *
     * @param property object property name to use from the element in the original result set for the new result set.
     * @returns New API instance with the values in the result retrieved from the source object properties defined by the property being plucked.
     */
    pluck(property: number | string): Api<any>;

    /**
     * Remove the last item from an API instance's result set.
     * 
     * @returns Item removed form the result set (was previously the last item in the result set).
     */
    pop(): any;

    /**
     * Add one or more items to the end of an API instance's result set.
     *
     * @param value_1 Item to add to the API instance's result set.
     * @returns The length of the modified API instance
     */
    push(value_1: any, ...value_2: any[]): number;

    /**
     * Order Methods / object
     */
    order: ApiOrder;

    /**
     * Apply a callback function against and accumulator and each element in the Api's result set (left-to-right).
     *
     * @param fn Callback function which is called for each item in the API instance result set. The callback is called with four parameters.
     * @param initialValue Value to use as the first argument of the first call to the fn callback.
     * @returns Result from the final call to the fn callback function.
     */
    reduce(fn: ((current: number, value: any, index: number, dt: Api<any>) => number), initialValue?: any): any;

    /**
     * Apply a callback function against and accumulator and each element in the Api's result set (right-to-left).
     *
     * @param fn Callback function which is called for each item in the API instance result set. The callback is called with four parameters.
     * @param initialValue Value to use as the first argument of the first call to the fn callback.
     * @returns Result from the final call to the fn callback function.
     */
    reduceRight(fn: ((current: number, value: any, index: number, dt: Api<any>) => number), initialValue?: any): any;

    /**
     * Reverse the result set of the API instance and return the original array.
     * 
     * @returns The original API instance with the result set in reversed order.
     */
    reverse(): Api<any>;

    /**
     * Row Methods / object
     */
    row: ApiRow<T>;

    /**
     * Rows Methods / object
     */
    rows: ApiRows<T>;

    /**
     * Get current search
     * 
     * @returns The currently applied global search. This may be an empty string if no search is applied.
     */
    search(): string;

    /**
     * Set the global search to use on the table. Note this doesn't actually perform the search.
     *
     * @param input Search string to apply to the table.
     * @param regex Treat as a regular expression (true) or not (default, false).
     * @param smart Perform smart search.
     * @param caseInsen Do case-insensitive matching (default, true) or not (false).
     * @returns DataTables API instance
     */
    search(input: string, regex?: boolean, smart?: boolean, caseInsen?: boolean): Api<any>;

    /**
     * Obtain the table's settings object
     * 
     * @returns DataTables API instance with the settings objects for the tables in the context in the result set
     */
    settings(): Api<InternalSettings>;

    /**
     * Remove the first item from an API instance's result set.
     * 
     * @returns Item removed form the result set (was previously the first item in the result set).
     */
    shift(): any;

    /**
     * Create an independent copy of the API instance.
     * 
     * @returns DataTables API instance
     */
    slice(): Api<any>;

    /**
     * Sort the elements of the API instance's result set.
     *
     * @param fn This is a standard Javascript sort comparison function. It accepts two parameters.
     * @returns The original API instance with the result set sorted as defined by the sorting conditions used.
     */
    sort(fn?: ((value1: any, value2: any) => number)): Api<Array<any>>;

    /**
     * Modify the contents of an Api instance's result set, adding or removing items from it as required.
     *
     * @param index Index at which to start modifying the Api instance's result set.
     * @param howMany Number of elements to remove from the result set.
     * @param value_1 Item to add to the result set at the index specified by the first parameter.
     * @returns An array of the items which were removed. If no elements were removed, an empty array is returned.
     */
    splice(index: number, howMany: number, value_1?: any, ...value_2: any[]): any[];

    /**
     * Page Methods / object
     */
    state: ApiState<T>;

    /**
     * Select a table based on a selector from the API's context
     *
     * @param tableSelector Table selector.
     * @returns DataTables API instance with selected table in its context.
     */
    table(tableSelector?: any): ApiTableMethods<T>;

    /**
     * Select tables based on the given selector
     *
     * @param tableSelector Table selector.
     * @returns DataTables API instance with all tables in the current context.
     */
    tables(tableSelector?: any): ApiTablesMethods<T>;

    /**
     * Convert the API instance to a jQuery object, with the objects from the instance's result set in the jQuery result set.
     * 
     * @returns jQuery object which contains the values from the API instance's result set.
     */
    to$(): JQuery;

    /**
     * Create a native Javascript array object from an API instance.
     * 
     * @returns Javascript array which contains the values from the API instance's result set.
     */
    toArray(): any[];

    /**
     * Convert the API instance to a jQuery object, with the objects from the instance's result set in the jQuery result set.
     * 
     * @returns jQuery object which contains the values from the API instance's result set.
     */
    toJQuery(): JQuery;

    /**
     * Create a new API instance containing only the unique items from a the elements in an instance's result set.
     * 
     * @returns New Api instance which contains the unique items from the original instance's result set, in its own result set.
     */
    unique(): Api<any>;

    /**
     * Add one or more items to the start of an API instance's result set.
     *
     * @param value_1 Item to add to the API instance's result set.
     * @returns The length of the modified API instance
     */
    unshift(value_1: any, ...value_2: any[]): number;
}


export interface ApiSelectorModifier {
    /**
     * The order modifier provides the ability to control which order the rows are processed in.
     * Values: 'current', 'applied', 'index',  'original'
     */
    order?: string;

    /**
     * The search modifier provides the ability to govern which rows are used by the selector using the search options that are applied to the table.
     * Values: 'none', 'applied', 'removed'
     */
    search?: string;

    /**
     * The searchPlaceholder modifier provides the ability to provide informational text for an input control when it has no value.
     */
    searchPlaceholder?: string;

    /**
     * The page modifier allows you to control if the selector should consider all data in the table, regardless of paging, or if only the rows in the currently disabled page should be used.
     * Values: 'all', 'current'
     */
    page?: string;
}

export interface AjaxMethods extends Api<any> {
    /**
     * Reload the table data from the Ajax data source.
     *
     * @param callback Function which is executed when the data as been reloaded and the table fully redrawn.
     * @param resetPaging Reset (default action or true) or hold the current paging position (false).
     * @returns DataTables Api instance
     */
    load(callback?: ((json: any) => void), resetPaging?: boolean): Api<any>;
}




export interface ApiPageInfo {
    page: number;
    pages: number;
    start: number;
    end: number;
    length: number;
    recordsTotal: number;
    recordsDisplay: number;
    serverSide: boolean;
}

export interface State {
    time: number;
    start: number;
    length: number;
    order: Array<Array<(string | number)>>;
    search: ConfigSearch;
    columns: Array<{
        search: ConfigSearch;
        visible: boolean;
    }>;
}

/**
 * "table" - loop over the context's (i.e. the tables) for the instance
 * 
 * @param settings Table settings object
 * @param counter Loop counter
 */
type InteratorTable = (settings: InternalSettings, counter: number) => any;

/**
 * "cell" - loop over each table and cell in the result set
 * 
 * @param settings Table settings object
 * @param rowIndex Row index
 * @param columnIndex Column index
 * @param tableCounter Table counter (outer)
 * @param cellCounter Cell counter (inner)
 */        
type InteratorCell = (settings: InternalSettings, rowIndex: number, columnIndex: number, tableCounter: number, cellCounter: number) => any;

/**
 * "columns" - loop over each item in the result set
 * 
 * @param settings Table settings object
 * @param resultItem Result set item
 * @param counter Loop counter
 */
type InteratorColumns = (settings: InternalSettings, resultItem: any, counter: number) => any;

/**
 * "column" - loop over each table and column in the result set
 * 
 * @param settings Table settings object
 * @param columnIndex Column index 
 * @param tableCounter Table counter (outer)
 * @param columnCounter Column counter (inner)
 */
type InteratorColumn = (settings: InternalSettings, columnIndex: number, tableCounter: number, columnCounter: number) => any;

/**
 * "column-rows" - loop over each table, column and row in the result set applying selector-modifier.
 * 
 * @param settings Table settings object
 * @param columnIndex Column index
 * @param tableCounter Table counter (outer)
 * @param columnCounter Column counter (inner)
 * @param rowIndexes Row indexes
 */
type InteratorColumnRows = (settings: InternalSettings, columnIndex: number, tableCounter: number, columnCounter: number, rowIndexes: number[]) => any;

/**
 * "row" - loop over each table and row in the result set
 * 
 * @param settings Table settings object
 * @param rowIndex Row index
 * @param tableCounter Table counter (outer)
 * @param rowCounter Row counter (inner)
 */
type InteratorRow = (settings: InternalSettings, rowIndex: number, tableCounter: number, rowCounter: number) => any;

/**
 * "rows" - loop over each item in the result set
 * 
 * @param settings Table settings object
 * @param resultItem Result set item
 * @param counter Loop counter
 */
type InteratorRows = (settings: InternalSettings, resultItem: any, counter: number) => any;

export interface ApiAjax {
    /**
     * Get the latest JSON data obtained from the last Ajax request DataTables made
     * 
     * @returns JSON object that was last loaded/
     */
    json(): object;

    /**
     * Get the data submitted by DataTables to the server in the last Ajax request
     * 
     * @returns object containing the data submitted by DataTables
     */
    params(): object;

    /**
     * Reload the table data from the Ajax data source.
     *
     * @param callback Function which is executed when the data as been reloaded and the table fully redrawn.
     * @param resetPaging Reset (default action or true) or hold the current paging position (false).
     * @returns DataTables Api
     */
    reload(callback?: ((json: any) => void), resetPaging?: boolean): Api<any>;

    /**
     * Reload the table data from the Ajax data source
     * 
     * @returns URL set as the Ajax data source for the table.
     */
    url(): string;

    /**
     * Reload the table data from the Ajax data source
     *
     * @param url URL to set to be the Ajax data source for the table.
     * @returns DataTables Api instance for chaining or further ajax.url() methods
     */
    url(url: string): AjaxMethods;
}

export interface ApiPage {
    /**
     * Get the current page of the table.
     * 
     * @returns Currently displayed page number
     */
    (): number;

    /**
     * Set the current page of the table.
     *
     * @param page Index or 'first', 'next', 'previous', 'last'
     * @returns DataTables API instance
     */
    (page: number | string): Api<any>;

    /**
     * Get paging information about the table
     * 
     * @returns Object with information about the table's paging state.
     */
    info(): ApiPageInfo;

    /**
     * Get the table's page length.
     * 
     * @returns Current page length.
     */
    len(): number;

    /**
     * Set the table's page length.
     *
     * @param length Page length to set. use -1 to show all records.
     * @returns DataTables API instance.
     */
    len(length: number): Api<any>;
}

export interface ApiOrder {
    /**
     * Get the ordering applied to the table.
     * 
     * @returns Array of arrays containing information about the currently applied sort. This 2D array is the same format as the array used for setting the order to apply to the table
     */
    (): Array<Array<(string | number)>>;

    /**
     * Set the ordering applied to the table.
     *
     * @param order Order Model
     * @returns DataTables Api instance
     */
    (order?: Array<(string | number)> | Array<Array<(string | number)>>): Api<any>;
    (order: Array<(string | number)>, ...args: any[]): Api<any>;

    /**
     * Get the fixed ordering that is applied to the table. If there is more than one table in the API's context,
     * the ordering of the first table will be returned only (use table() if you require the ordering of a different table in the API's context).
     * @returns object describing the ordering that is applied to the table
     */
    fixed(): OrderFixed;

    /**
     * Set the table's fixed ordering. Note this doesn't actually perform the order, but rather queues it up - use draw() to perform the ordering.
     * 
     * @param order Used to indicate whether the ordering should be performed before or after the users own ordering.
     * @returns DataTables Api instance
     */
    fixed(order: OrderFixed): Api<any>;

    /**
     * Add an ordering listener to an element, for a given column.
     *
     * @param node Selector
     * @param column Column index
     * @param callback Callback function
     * @returns DataTables API instance with the current order in the result set
     */
    listener(node: string | Node | JQuery, column: number, callback: (() => void)): Api<any>;
}

export interface ApiState<T> {
    /**
     * Get the last saved state of the table
     * 
     * @returns State saved object
     */
    (): State;

    /**
     * Clear the saved state of the table.
     * 
     * @returns The API instance that was used, available for chaining.
     */
    clear(): Api<any>;

    /**
     * Get the table state that was loaded during initialisation.
     * 
     * @returns State saved object. See state() for the object format.
     */
    loaded(): State;

    /**
     * Trigger a state save.
     * 
     * @returns The API instance that was used, available for chaining.
     */
    save(): Api<T>;
}



export interface ApiCell<T> {
    /**
     * Select the cell found by a cell selector
     *
     * @param cellSelector Cell selector.
     * @param Option used to specify how the cells should be ordered, and if paging or filtering
     * @returns DataTables API instance with selected cell
     */
    (cellSelector: CellSelector, modifier?: ApiSelectorModifier): ApiCellMethods<T>;

    /**
     * Select the cell found by a cell selector
     *
     * @param rowSelector Row selector.
     * @param columnSelector Column selector.
     * @param Option used to specify how the cells should be ordered, and if paging or filtering
     * @returns DataTables API instance with selected cell
     */
    (rowSelector: CellSelector, columnSelector: any, modifier?: ApiSelectorModifier): ApiCellMethods<T>;
}

export interface ApiCellMethods<T> extends Omit<Api<T>, 'render'> {
    /**
     * Get the DataTables cached data for the selected cell
     *
     * @param type Specify which cache the data should be read from. Can take one of two values: search or order
     * @returns DataTables API instance with the cached data for each selected cell in the result set
     */
    cache(type: string): Api<T>;

    /**
     * Get data for the selected cell
     * 
     * @returns the data from the cell
     */
    data(): any;

    /**
     * Get data for the selected cell
     *
     * @param data Value to assign to the data for the cell
     * @returns DataTables Api instance
     */
    data(data: any): Api<T>;

    /**
     * Get index information about the selected cell
     * 
     * @returns Object with index information for the selected cell.
     */
    index(): CellIdxWithVisible;

    /**
     * Invalidate the data held in DataTables for the selected cell
     *
     * @param source Data source to read the new data from.
     * @returns DataTables API instance with selected cell references in the result set
     */
    invalidate(source?: string): Api<T>;

    /**
     * Get the DOM element for the selected cell
     * 
     * @returns The TD / TH cell the selector resolved to
     */
    node(): Node;

    /**
     * Get data for the selected cell
     *
     * @param type Data type to get. This can be one of: 'display', 'filter', 'sort', 'type'
     * @returns Rendered data for the requested type
     */
    render(type: string): any;
}

export interface ApiCells<T> {
    /**
     * Select all cells
     *
     * @param Option used to specify how the cells should be ordered, and if paging or filtering
     * @returns DataTables API instance with selected cells
     */
    (modifier?: ApiSelectorModifier): ApiCellsMethods<T>;

    /**
     * Select cells found by a cell selector
     *
     * @param cellSelector Cell selector.
     * @param Option used to specify how the cells should be ordered, and if paging or filtering
     * @returns DataTables API instance with selected cells
     */
    (cellSelector: CellSelector, modifier?: ApiSelectorModifier): ApiCellsMethods<T>;
 
    /**
     * Select cells found by both row and column selectors
     *
     * @param rowSelector Row selector.
     * @param columnSelector Column selector.
     * @param Option used to specify how the cells should be ordered, and if paging or filtering
     * @returns DataTables API instance with selected cells
     */
    (rowSelector: RowSelector<T>, columnSelector: CellSelector, modifier?: ApiSelectorModifier): ApiCellsMethods<T>;
}

export interface ApiCellsMethods<T> extends Omit<Api<T>, 'data' | 'render'> {
    /**
     * Get the DataTables cached data for the selected cells
     *
     * @param type Specify which cache the data should be read from. Can take one of two values: search or order
     * @returns DataTables API instance with the cached data for each selected cell in the result set
     */
    cache(type: string): Api<T>;

    /**
     * Get data for the selected cells
     * 
     * @returns DataTables API instance with data for each cell in the selected columns in the result set. This is a 1D array with each entry being the data for the cells from the selected column.
     */
    data(): Api<Array<T>>;

    /**
     * Iterate over each selected cell, with the function context set to be the cell in question. Since: DataTables 1.10.6
     *
     * @param fn Function to execute for every cell selected.
     */
    every(fn: (this: ApiCellsMethods<T>, cellRowIdx: number, cellColIdx: number, tableLoop: number, cellLoop: number) => void): Api<any>;

    /**
     * Get index information about the selected cells
     */
    indexes(): Api<CellIdxWithVisible>;

    /**
     * Invalidate the data held in DataTables for the selected cells
     *
     * @param source Data source to read the new data from.
     * @returns DataTables API instance with selected cell references in the result set
     */
    invalidate(source?: string): Api<T>;

    /**
     * Get the DOM elements for the selected cells
     */
    nodes(): Api<Node>;

    /**
     * Get data for the selected cell
     *
     * @param type Data type to get. This can be one of: 'display', 'filter', 'sort', 'type'
     * @returns Rendered data for the requested type
     */
    render(type: string): any;
}




export interface ApiColumn {
    /**
     * Select the column found by a column selector
     *
     * @param cellSelector Cell selector.
     * @param Option used to specify how the cells should be ordered, and if paging or filtering in the table should be taken into account.
     */
    (columnSelector: ColumnSelector, modifier?: ApiSelectorModifier): ApiColumnMethods;

    /**
     * Convert from the input column index type to that required.
     *
     * @param type The type on conversion that should take place: 'fromVisible', 'toData', 'fromData', 'toVisible'
     * @param index The index to be converted
     * @returns Calculated column index
     */
    index(type: string, index: number): number;
}

export interface ApiColumnMethods {
    /**
     * Get the DataTables cached data for the selected column(s)
     *
     * @param type Specify which cache the data should be read from. Can take one of two values: search or order
     * @return DataTables Api instance with an caches data for the selected column(s)
     */
    cache(type: string): Api<any>;

    /**
     * Get the data for the cells in the selected column.
     * 
     * @returns DataTables API instance with data for each cell in the selected columns in the result set. This is a 1D array with each entry being the data for the cells from the selected column.
     */
    data(): Api<Array<any>>;

    /**
     * Get the data source property for the selected column.
     * 
     * @returns the data source property
     */
    dataSrc(): number | string | (() => string);

    /**
     * Get the footer th / td cell for the selected column(s).
     * 
     * @returns HTML element for the footer of the column(s)
     */
    footer(): HTMLElement;

    /**
     * Get the header th / td cell for a column(s).
     * 
     * @returns HTML element for the header of the column(s)
     */
    header(): HTMLElement;

    /**
     * Get the column index of the selected column.
     *
     * @param type Specify if you want to get the column data index (default) or the visible index (visible).
     * @returns The column index for the selected column.
     */
    index(type?: string): number;

    /**
     * Obtain the th / td nodes for the selected column
     * 
     * @returns DataTables API instance with each cell's node from the selected columns in the result set. This is a 1D array with each entry being the node for the cells from the selected column.
     */
    nodes(): Api<Array<Node>>;

    /**
     * Order the table, in the direction specified, by the column selected by the column() selector.
     *
     * @param direction Direction of sort to apply to the selected column - desc (descending) or asc (ascending).
     * @returns DataTables API instance
     */
    order(direction: string): Api<any>;

    /**
     * Get the currently applied column search.
     * 
     * @returns the currently applied column search.
     */
    search(): string;

    /**
     * Set the search term for the matched columns.
     *
     * @param input Search string to apply.
     * @param regex Treat as a regular expression (true) or not (default, false).
     * @param smart Perform smart search.
     * @param caseInsen Do case-insensitive matching (default, true) or not (false).
     * @returns DataTables API instance
     */
    search(input: string, regex?: boolean, smart?: boolean, caseInsen?: boolean): Api<any>;

    /**
     * Get the visibility of the selected column.
     * 
     * @returns true if the column is visible, false if it is not.
     */
    visible(): boolean;

    /**
     * Set the visibility of the selected column.
     *
     * @param show Specify if the column should be visible (true) or not (false).
     * @param redrawCalculations Indicate if DataTables should recalculate the column layout (true - default) or not (false).
     * @returns DataTables API instance with selected column in the result set.
     */
    visible(show: boolean, redrawCalculations?: boolean): Api<any>;
}

export interface ApiColumns<T> {
    /**
     * Select all columns
     *
     * @param Option used to specify how the cells should be ordered, and if paging or filtering in the table should be taken into account.
     * @returns DataTables API instance with selected columns in the result set.
     */
    (modifier?: ApiSelectorModifier): ApiColumnsMethods | Api<Array<any>>;
    
    /**
     * Select columns found by a cell selector
     *
     * @param cellSelector Cell selector.
     * @param Option used to specify how the cells should be ordered, and if paging or filtering in the table should be taken into account.
     * @returns DataTables API instance with selected columns
     */
    (columnSelector: ColumnSelector, modifier?: ApiSelectorModifier): ApiColumnsMethods;

    /**
     * Recalculate the column widths for layout.
     * 
     * @returns DataTables API instance.
     */
    adjust(): Api<T>;
}


export interface ApiColumnsMethods {
    /**
     * Get the DataTables cached data for the selected columna
     *
     * @param type Specify which cache the data should be read from. Can take one of two values: search or order
     * @return DataTables Api instance with an caches data for the selected columna
     */
    cache(type: string): Api<any>;

    /**
     * Obtain the data for the columns from the selector
     * 
     * @returns DataTables API instance with data for each cell in the selected columns in the result set. This is a 2D array with the top level array entries for each column matched by the columns() selector.
     */
    data(): Api<Array<Array<any>>>;

    /**
     * Get the data source property for the selected columns.
     * 
     * @returns API instance with the result set containing the data source parameters for the selected columns as configured by
     */
    dataSrc(): Api<any>;

    /**
     * Iterate over each selected column, with the function context set to be the column in question. Since: DataTables 1.10.6
     *
     * @param fn Function to execute for every column selected.
     * @returns DataTables API instance of the selected columns.
     */
    every(fn: (this: ApiColumnMethods, colIdx: number, tableLoop: number, colLoop: number) => void): Api<any>;

    /**
     * Get the footer th / td cell for the selected columna.
     * 
     * @returns HTML element for the footer of the columna
     */
    footer(): HTMLElement;

    /**
     * Get the column indexes of the selected columns.
     *
     * @param type Specify if you want to get the column data index (default) or the visible index (visible).
     * @returns DataTables API instance with selected columns' indexes in the result set.
     */
    indexes(type?: string): Api<Array<number>>;

    /**
     * Get the header th / td cell for a columna.
     * 
     * @returns HTML element for the header of the columna
     */
    header(): HTMLElement;

    /**
     * Obtain the th / td nodes for the selected columns
     * 
     * @returns DataTables API instance with each cell's node from the selected columns in the result set. This is a 2D array with the top level array entries for each column matched by the columns() selector.
     */
    nodes(): Api<Array<Array<Node>>>;

    /**
     * Order the table, in the direction specified, by the columns selected by the column() selector.
     *
     * @param direction Direction of sort to apply to the selected columna - desc (descending) or asc (ascending).
     * @returns DataTables API instance
     */
    order(direction: string): Api<any>;

    /**
     * Get the currently applied columns search.
     * 
     * @returns the currently applied columns search.
     */
    search(): Api<Array<string>>;

    /**
     * Set the search term for the columns from the selector. Note this doesn't actually perform the search.
     * 
     * @param input Search string to apply to the selected columns.
     * @param regex Treat as a regular expression (true) or not (default, false).
     * @param smart Perform smart search (default, true) or not (false). 
     * @param caseInsen Do case-insensitive matching (default, true) or not (false).
     * @returns DataTables Api instance.
     */
    search(input: string, regex?: boolean, smart?: boolean, caseInsen?: boolean): Api<any>;

    /**
     * Get the visibility of the selected columns.
     * 
     * @returns true if the columns is visible, false if it is not.
     */
    visible(): boolean;

    /**
     * Set the visibility of the selected columns.
     *
     * @param show Specify if the columns should be visible (true) or not (false).
     * @param redrawCalculations Indicate if DataTables should recalculate the columns layout (true - default) or not (false).
     * @returns DataTables API instance with selected columns in the result set.
     */
    visible(show: boolean, redrawCalculations?: boolean): Api<any>;
}


export interface ApiRowChildMethods<T> {
    /**
     * Get the child row(s) that have been set for a parent row
     * 
     * @returns Query object with the child rows for the parent row in its result set, or undefined if there are no child rows set for the parent yet.
     */
    (): JQuery;

    /**
     * Get the child row(s) that have been set for a parent row
     *
     * @param showRemove This parameter can be given as true or false
     * @returns DataTables Api instance.
     */
    (showRemove: boolean): RowChildMethods<T>;

    /**
     * Set the data to show in the child row(s). Note that calling this method will replace any child rows which are already attached to the parent row.
     *
     * @param data The data to be shown in the child row can be given in multiple different ways.
     * @param className Class name that is added to the td cell node(s) of the child row(s). As of 1.10.1 it is also added to the tr row node of the child row(s).
     * @returns DataTables Api instance
     */
    (data: (string | Node | JQuery) | Array<(string | number | JQuery)>, className?: string): RowChildMethods<T>;

    /**
     * Hide the child row(s) of a parent row
     * 
     * @returns DataTables API instance.
     */
    hide(): Api<any>;

    /**
     * Check if the child rows of a parent row are visible
     * 
     * @returns boolean indicating whether the child rows are visible.
     */
    isShown(): boolean;

    /**
     * Remove child row(s) from display and release any allocated memory
     * 
     * @returns DataTables API instance.
     */
    remove(): Api<any>;

    /**
     * Show the child row(s) of a parent row
     * 
     * @returns DataTables API instance.
     */
    show(): Api<any>;
}

export interface RowChildMethods<T> extends Api<T> {
    /**
     * Hide the child row(s) of a parent row
     * 
     * @returns DataTables API instance.
     */
    hide(): Api<any>;

    /**
     * Remove child row(s) from display and release any allocated memory
     * 
     * @returns DataTables API instance.
     */
    remove(): Api<any>;

    /**
     * Make newly defined child rows visible
     * 
     * @returns DataTables API instance.
     */
    show(): Api<any>;
}

export interface ApiRow<T> {
    /**
     * Select a row found by a row selector
     *
     * @param rowSelector Row selector.
     * @param Option used to specify how the cells should be ordered, and if paging or filtering in the table should be taken into account.
     * @returns DataTables API instance with selected row in the result set
     */
    (rowSelector: RowSelector<T>, modifier?: ApiSelectorModifier): ApiRowMethods<T>;

    /**
     * Add a new row to the table using the given data
     *
     * @param data Data to use for the new row. This may be an array, object or Javascript object instance, but must be in the same format as the other data in the table+
     * @returns DataTables API instance with the newly added row in its result set.
     */
    add(data: any[] | object): ApiRowMethods<T>;
}

export interface ApiRowMethods<T> extends Omit<Api<T>, 'data'> {
    /**
     * Get the DataTables cached data for the selected row(s)
     *
     * @param type Specify which cache the data should be read from. Can take one of two values: search or order
     * @returns DataTables API instance with data for each cell in the selected row in the result set. This is a 1D array with each entry being the data for the cells from the selected row.
     */
    cache(type: string): Api<Array<any>> | Api <Array<Array<any>>>;

    /**
     * Order Methods / object
     */
    child: ApiRowChildMethods<T>;

    /**
     * Get the data for the selected row
     * 
     * @returns Data source object for the data source of the row.
     */
    data(): T;

    /**
     * Set the data for the selected row
     *
     * @param d Data to use for the row.
     * @returns DataTables API instance with the row retrieved by the selector in the result set.
     */
    data(d: any[] | object): Api<T>;

    /**
     * Get the id of the selected row. Since: 1.10.8
     *
     * @param hash true - Append a hash (#) to the start of the row id. This can be useful for then using the id as a selector
     * false - Do not modify the id value.
     * @returns Row id. If the row does not have an id available 'undefined' will be returned.
     */
    id(hash?: boolean): string;

    /**
     * Get the row index of the row column.
     * 
     * @returns Row index
     */
    index(): number;

    /**
     * Obtain the th / td nodes for the selected row(s)
     *
     * @param source Data source to read the new data from. Values: 'auto', 'data', 'dom'
     */
    invalidate(source?: string): Api<Array<any>>;

    /**
     * Obtain the tr node for the selected row
     * 
     * @returns tr element of the selected row or null if the element is not yet available
     */
    node(): Node;

    /**
     * Delete the selected row from the DataTable.
     * 
     * @returns DataTables API instance with removed row reference in the result set
     */
    remove(): Api<Node>;
}

export interface ApiRows<T> {
    /**
     * Select all rows
     *
     * @param Option used to specify how the cells should be ordered, and if paging or filtering in the table should be taken into account.
     * @returns DataTables API instance with selected rows
     */
    (modifier?: ApiSelectorModifier): ApiRowsMethods<T>;

    /**
     * Select rows found by a row selector
     *
     * @param cellSelector Row selector.
     * @param Option used to specify how the cells should be ordered, and if paging or filtering in the table should be taken into account.
     * @returns DataTables API instance with selected rows in the result set
     */
    (rowSelector: RowSelector<T>, modifier?: ApiSelectorModifier): ApiRowsMethods<T>;

    /**
     * Add new rows to the table using the data given
     *
     * @param data Array of data elements, with each one describing a new row to be added to the table
     * @returns DataTables API instance with the newly added rows in its result set.
     */
    add(data: T[]): ApiRowsMethods<T>;
}

export interface ApiRowsMethods<T> extends Api<T> {
    /**
     * Get the DataTables cached data for the selected row(s)
     *
     * @param type Specify which cache the data should be read from. Can take one of two values: search or order
     * @returns DataTables API instance with data for each cell in the selected row in the result set. This is a 1D array with each entry being the data for the cells from the selected row.
     */
    cache(type: string): Api<Array<any>> | Api <Array<Array<any>>>;

    /**
     * Get the data for the selected rows
     *
     * @returns DataTables API instance with data for each row from the selector in the result set.
     */
    data(): Api<T>;

    /**
     * Iterate over each selected row, with the function context set to be the row in question. Since: DataTables 1.10.6
     *
     * @param fn Function to execute for every row selected.
     * @returns DataTables API instance of the selected rows.
     */
    every(fn: (this: ApiRowMethods<T>, rowIdx: number, tableLoop: number, rowLoop: number) => void): Api<any>;

    /**
     * Get the ids of the selected rows. Since: 1.10.8
     *
     * @param hash true - Append a hash (#) to the start of each row id. This can be useful for then using the ids as selectors
     * false - Do not modify the id value.
     * @returns Api instance with the selected rows in its result set. If a row does not have an id available 'undefined' will be returned as the value.
     */
    ids(hash?: boolean): Api<Array<any>>;

    /**
     * Get the row indexes of the selected rows.
     * 
     * @returns DataTables API instance with selected row indexes in the result set.
     */
    indexes(): Api<Array<number>>;

    /**
     * Obtain the th / td nodes for the selected row(s)
     *
     * @param source Data source to read the new data from. Values: 'auto', 'data', 'dom'
     */
    invalidate(source?: string): Api<Array<any>>;

    /**
     * Obtain the tr nodes for the selected rows
     * 
     * @returns DataTables API instance with each row's node from the selected rows in the result set.
     */
    nodes(): Api<Array<Node>>;

    /**
     * Delete the selected rows from the DataTable.
     * 
     * @returns DataTables API instance with references for the removed rows in the result set
     */
    remove(): Api<Array<any>>;
}


export interface ApiTableMethods<T> extends Api<T> {
    /**
     * Get the tfoot node for the table in the API's context
     * 
     * @returns HTML tbody node.
     */
    footer(): Node;

    /**
     * Get the thead node for the table in the API's context
     * 
     * @returns HTML thead node.
     */
    header(): Node;

    /**
     * Get the tbody node for the table in the API's context
     * 
     * @returns HTML tfoot node.
     */
    body(): Node;

    /**
     * Get the div container node for the table in the API's context
     * 
     * @returns HTML div node.
     */
    container(): Node;

    /**
     * Get the table node for the table in the API's context
     * 
     * @returns HTML table node for the selected table.
     */
    node(): Node;
}

export interface ApiTablesMethods<T> extends Api<T> {
    /**
     * Get the tfoot nodes for the tables in the API's context
     * 
     * @returns Array of HTML tfoot nodes for all table in the API's context
     */
    footer(): Api<Array<Node>>;

    /**
     * Get the thead nodes for the tables in the API's context
     * 
     * @returns Array of HTML thead nodes for all table in the API's context
     */
    header(): Api<Array<Node>>;

    /**
     * Get the tbody nodes for the tables in the API's context
     * 
     * @returns Array of HTML tbody nodes for all table in the API's context
     */
    body(): Api<Array<Node>>;

    /**
     * Get the div container nodes for the tables in the API's context
     * 
     * @returns Array of HTML div nodes for all table in the API's context
     */
    containers(): Api<Array<Node>>;

    /**
     * Get the table nodes for the tables in the API's context
     * 
     * @returns Array of HTML table nodes for all table in the API's context
     */
    nodes(): Api<Array<Node>>;
}


















export interface DataTablesStatic {
    /**
     * Check if a table node is a DataTable already or not.
     *
     * Usage:
     * $.fn.dataTable.isDataTable("selector");
     * @param table The table to check.
     * @returns true the given table is a DataTable, false otherwise
     */
    isDataTable(table: string | Node | JQuery | Api<any>): boolean;

    /**
     * Helpers for `columns.render`.
     *
     * The options defined here can be used with the `columns.render` initialisation
     * option to provide a display renderer.
     */
    render: DataTablesStaticRender;

    /**
     * Get all DataTable tables that have been initialised - optionally you can select to get only currently visible tables and / or retrieve the tables as API instances.
     *
     * @param visible As a boolean value this options is used to indicate if you want all tables on the page should be returned (false), or visible tables only (true).
     * Since 1.10.8 this option can also be given as an object.
     * @returns Array or DataTables API instance containing all matching DataTables
     */
    tables(visible?: {
        /**
         * Get only visible tables (true) or all tables regardless of visibility (false).
         */
        visible: boolean;
    
        /**
         * Return a DataTables API instance for the selected tables (true) or an array (false).
         */
        api: boolean;
    } | boolean): Array<Api<any>>| Api<any>;

    /**
     * Version number compatibility check function
     *
     * Usage:
     * $.fn.dataTable.versionCheck("1.10.0");
     * @param version Version string
     * @returns true if this version of DataTables is greater or equal to the required version, or false if this version of DataTales is not suitable
     */
    versionCheck(version: string): boolean;

    /**
     * Utils
     */
    util: DataTablesStaticUtil;

    /**
     * Get DataTable API instance
     *
     * @param table Selector string for table
     */
    Api: ApiStatic;

    /**
     * Default Settings
     */
    defaults: Config;

    /**
     * Default Settings
     */
    ext: DataTablesStaticExt;
}

export interface ApiStatic {
    /**
     * Create a new API instance to an existing DataTable. Note that this
     * does not create a new DataTable.
     */
    new (selector: string | Node | Node[] | JQuery | InternalSettings): Api<any>;

    register<T=any>(name: string, fn: Function): T;
    registerPlural<T=any>(pluralName: string, singleName: string, fn: Function): T;
}

export interface OrderFixed {
    /**
     * Two-element array:
     * 0: Column index to order upon.
     * 1: Direction so order to apply ("asc" for ascending order or "desc" for descending order).
     */
    pre?: any[];

    /**
     * Two-element array:
     * 0: Column index to order upon.
     * 1: Direction so order to apply ("asc" for ascending order or "desc" for descending order).
     */
    post?: any[];
}

export interface DataTablesStaticRender {
    /**
     * Format an ISO8061 date in auto locale detected format
     */
    date(): ObjectColumnRender;

    /**
     * Format an ISO8061 date value using the specified format
     * @param to Display format
     * @param locale Locale
     * @param def Default value if empty
     */
    date(to: string, locale?: string): ObjectColumnRender;

    /**
     * Format a date value
     * @param from Data format
     * @param to Display format
     * @param locale Locale
     * @param def Default value if empty
     */
    date(from?: string, to?: string, locale?: string, def?: string): ObjectColumnRender;

    /**
     * Format an ISO8061 datetime in auto locale detected format
     */
    datetime(): ObjectColumnRender;

    /**
     * Format an ISO8061 datetime value using the specified format
     * @param to Display format
     * @param locale Locale
     * @param def Default value if empty
     */
    datetime(to: string, locale?: string): ObjectColumnRender;

    /**
     * Format a datetime value
     * @param from Data format
     * @param to Display format
     * @param locale Locale
     * @param def Default value if empty
     */
    datetime(from?: string, to?: string, locale?: string, def?: string): ObjectColumnRender;

    /**
     * Render a number with auto-detected locale thousands and decimal
     */
    number(): ObjectColumnRender;

    /**
     * Will format numeric data (defined by `columns.data`) for display, retaining the original unformatted data for sorting and filtering.
     *
     * @param thousands Thousands grouping separator. `null` for auto locale
     * @param decimal Decimal point indicator. `null` for auto locale
     * @param precision Integer number of decimal points to show.
     * @param prefix Prefix (optional).
     * @param postfix Postfix (/suffix) (optional).
     */
    number(thousands: string | null, decimal: string | null, precision: number, prefix?: string, postfix?: string): ObjectColumnRender;

    /**
     * Escape HTML to help prevent XSS attacks. It has no optional parameters.
     */
    text(): ObjectColumnRender;

    /**
     * Format an ISO8061 date in auto locale detected format
     */
    time(): ObjectColumnRender;

    /**
     * Format an ISO8061 time value using the specified format
     * @param to Display format
     * @param locale Locale
     * @param def Default value if empty
     */
    time(to: string, locale?: string): ObjectColumnRender;

    /**
     * Format a time value
     * @param from Data format
     * @param to Display format
     * @param locale Locale
     * @param def Default value if empty
     */
    time(from?: string, to?: string, locale?: string, def?: string): ObjectColumnRender;
}

export interface DataTablesStaticUtil {
    /**
     * Escape special characters in a regular expression string. Since: 1.10.4
     *
     * @param str String to escape
     * @returns Escaped string
     */
    escapeRegex(str: string): string;

    /**
     * Create a read function from a descriptor. Since 1.11
     * @param source A descriptor that is used to define how to read the data from the source object.
     */
    get<T=any, D=any>(source: string | object | Function | null): ((data: D, type: string, val: T, meta: CellMetaSettings) => T);

    /**
     * Create a write function from a descriptor. Since 1.11
     * @param source A descriptor that is used to define how to write data to a source object
     */
    set<T=any, D=any>(source: string | object | Function | null): ((data: D, val: T, meta: CellMetaSettings) => void);

    /**
     * Throttle the calls to a method to reduce call frequency. Since: 1.10.3
     *
     * @param fn Function
     * @param period ms
     * @returns Wrapper function that can be called and will automatically throttle calls to the passed in function to the given period.
     */
    throttle(fn: (data: any) => void, period?: number): (() => void);
}



export interface AjaxData {
    draw: number;
    start: number;
    length: number;
    data: any;
    order: AjaxDataOrder[];
    columns: AjaxDataColumn[];
    search: AjaxDataSearch;
}

export interface AjaxDataSearch {
    value: string;
    regex: boolean;
}

export interface AjaxDataOrder {
    column: number;
    dir: string;
}

export interface AjaxDataColumn {
    data: string | number;
    name: string;
    searchable: boolean;
    orderable: boolean;
    search: AjaxDataSearch;
}

export interface AjaxResponse {
    draw?: number;
    recordsTotal?: number;
    recordsFiltered?: number;
    data: any;
    error?: string;
}

interface AjaxSettings extends JQueryAjaxSettings {
    /**
     * Add or modify data submitted to the server upon an Ajax request.
     */
    data?: object | FunctionAjaxData;

    /**
     * Data property or manipulation method for table data.
     */
    dataSrc?: string | ((data: any) => any[]);
}

interface FunctionColumnData {
    (row: any, type: 'set', s: any, meta: CellMetaSettings): void;
    (row: any, type: 'display' | 'sort' | 'filter' | 'type', s: undefined, meta: CellMetaSettings): any;
}

interface ObjectColumnData {
    _: string | number | FunctionColumnData;
    filter?: string | number | FunctionColumnData;
    display?: string | number | FunctionColumnData;
    type?: string | number | FunctionColumnData;
    sort?: string | number | FunctionColumnData;
}

interface ObjectColumnRender {
    _?: string | number | FunctionColumnRender;
    filter?: string | number | FunctionColumnRender;
    display?: string | number | FunctionColumnRender;
    type?: string | number | FunctionColumnRender;
    sort?: string | number | FunctionColumnRender;
}

interface CellMetaSettings {
    row: number;
    col: number;
    settings: InternalSettings;
}

export interface DataTablesStaticExt {
    builder: string;
    buttons: {[key: string]: object};
    classes: ExtClassesSettings;
    errMode: string;
    feature: any[];
    iApiIndex: number;
    internal: object;
    legacy: object;
    oApi: object;
    order: object;
    oSort: object;
    pager: object;
    renderer: object;
    search: any[];
    selector: object;
    /**
     * Type based plug-ins.
     */
    type: ExtTypeSettings;
}

/**
 * Classes used by DataTables. Used for styling integration. Note
 * that these all use legacy Hungarian notation.
 */
 export interface ExtClassesSettings {
    /**
     * Default Value:
     * dataTable
     */
    sTable?: string;

    /**
     * Default Value:
     * no-footer
     */
    sNoFooter?: string;

    /**
     * Default Value:
     * paginate_button
     */
    sPageButton?: string;

    /**
     * Default Value:
     * current
     */
    sPageButtonActive?: string;

    /**
     * Default Value:
     * disabled
     */
    sPageButtonDisabled?: string;

    /**
     * Default Value:
     * odd
     */
    sStripeOdd?: string;

    /**
     * Default Value:
     * even
     */
    sStripeEven?: string;

    /**
     * Default Value:
     * dataTables_empty
     */
    sRowEmpty?: string;

    /**
     * Default Value:
     * dataTables_wrapper
     */
    sWrapper?: string;

    /**
     * Default Value:
     * dataTables_filter
     */
    sFilter?: string;

    /**
     * Default Value:
     * dataTables_info
     */
    sInfo?: string;

    /**
     * Default Value:
     * dataTables_paginate paging_
     */
    sPaging?: string;

    /**
     * Default Value:
     * dataTables_length
     */
    sLength?: string;

    /**
     * Default Value:
     * dataTables_processing
     */
    sProcessing?: string;

    /**
     * Default Value:
     * sorting_asc
     */
    sSortAsc?: string;

    /**
     * Default Value:
     * sorting_desc
     */
    sSortDesc?: string;

    /**
     * Default Value:
     * sorting
     */
    sSortable?: string;

    /**
     * Default Value:
     * sorting_asc_disabled
     */
    sSortableAsc?: string;

    /**
     * Default Value:
     * sorting_desc_disabled
     */
    sSortableDesc?: string;

    /**
     * Default Value:
     * sorting_disabled
     */
    sSortableNone?: string;

    /**
     * Default Value:
     * sorting_
     */
    sSortColumn?: string;

    sFilterInput?: string;
    sLengthSelect?: string;

    /**
     * Default Value:
     * dataTables_scroll
     */
    sScrollWrapper?: string;

    /**
     * Default Value:
     * dataTables_scrollHead
     */
    sScrollHead?: string;

    /**
     * Default Value:
     * dataTables_scrollHeadInner
     */
    sScrollHeadInner?: string;

    /**
     * Default Value:
     * dataTables_scrollBody
     */
    sScrollBody?: string;

    /**
     * Default Value:
     * dataTables_scrollFoot
     */
    sScrollFoot?: string;

    /**
     * Default Value:
     * dataTables_scrollFootInner
     */
    sScrollFootInner?: string;

    sHeaderTH?: string;
    sFooterTH?: string;
    sSortJUIAsc?: string;
    sSortJUIDesc?: string;
    sSortJUI?: string;
    sSortJUIAscAllowed?: string;
    sSortJUIDescAllowed?: string;
    sSortJUIWrapper?: string;
    sSortIcon?: string;
    sJUIHeader?: string;
    sJUIFooter?: string;
}


export interface ExtTypeSettings {
    /**
     * Type detection functions for plug-in development.
     *
     * @see https://datatables.net/manual/plug-ins/type-detection
     */
    detect: FunctionExtTypeSettingsDetect[];

    /**
     * Type based ordering functions for plug-in development.
     *
     * @see https://datatables.net/manual/plug-ins/sorting
     * @default {}
     */
    order: any;

    /**
     * Type based search formatting for plug-in development.
     *
     * @default {}
     * @example
     *   $.fn.dataTable.ext.type.search['title-numeric'] = function ( d ) {
     *     return d.replace(/\n/g," ").replace( /<.*?>/g, "" );
     *   }
     */
    search: any;
}

/**
 * @param data Data from the column cell to be analysed.
 * @param DataTables settings object.
 */
type FunctionExtTypeSettingsDetect = (data: any, settings: InternalSettings) => (string | null);

type FunctionAjax = (data: object, callback: ((data: any) => void), settings: InternalSettings) => void;

type FunctionAjaxData = (data: AjaxData, settings: InternalSettings) => string | object;

type FunctionColumnRender = (data: any, type: any, row: any, meta: CellMetaSettings) => any;

type FunctionColumnCreatedCell = (cell: Node, cellData: any, rowData: any, row: number, col: number) => void;

type FunctionCreateRow = (row: Node, data: any[] | object, dataIndex: number, cells: Node[]) => void;

type FunctionDrawCallback = (settings: InternalSettings) => void;

type FunctionFooterCallback = (tfoot: Node, data: any[], start: number, end: number, display: any[]) => void;

type FunctionFormatNumber = (formatNumber: number) => void;

type FunctionHeaderCallback = (thead: Node, data: any[], start: number, end: number, display: any[]) => void;

type FunctionInfoCallback = (settings: InternalSettings, start: number, end: number, mnax: number, total: number, pre: string) => void;

type FunctionInitComplete = (settings: InternalSettings, json: object) => void;

type FunctionPreDrawCallback = (settings: InternalSettings) => void;

type FunctionRowCallback = (row: Node, data: any[] | object, index: number) => void;

type FunctionStateLoadCallback = (settings: InternalSettings) => void;

type FunctionStateLoaded = (settings: InternalSettings, data: object) => void;

type FunctionStateLoadParams = (settings: InternalSettings, data: object) => void;

type FunctionStateSaveCallback = (settings: InternalSettings, data: object) => void;

type FunctionStateSaveParams = (settings: InternalSettings, data: object) => void;
