// Type definitions for datatables.net-colReorder 1.4
// Project: https://datatables.net/extensions/colreorder/, https://datatables.net
// Definitions by: Andy Ma <https://github.com/andy-maca>
// Definitions: https://github.com/DefinitelyTyped/DefinitelyTyped
// TypeScript Version: 2.4

/// <reference types="jquery" />
/// <reference types="datatables.net"/>

declare namespace DataTables {
    interface Settings {
      /*
       * colReorder extension options
       */
      colReorder?: boolean | ColReorderSettings;
    }
  
    interface ColReorderSettings {
      /*
       * Number of columns (counting from the left) to disallow reordering of, '0' in default
       */
      fixedColumnsLeft?: number;
  
      /*
       * Number of columns (counting from the right) to disallow reordering of, '0' in default
       */
      fixedColumnsRight?: number;
  
      /*
       * Set a default order for the columns in the table
       */
      order?: number[];
  
      /*
       * Enable / disable live reordering of columns during a drag, 'true' in default
       */
      realtime?: boolean;
      /*
       * Callback after reorder
       */
      reorderCallback: () => void;
    }
  
    interface Api {
      colReorder: {
        /**
         * Disable end user ability to reorder columns.
         * 
         * @returns DataTables Api instance.
         */
        disable(): Api;

        /**
         * Enable and disable user ability to reorder columns in a table.
         * 
         * @param flag if true enable colReorder, if false disable.
         * @returns DataTables Api instance
         */
        enable(flag?: boolean): Api;

        /**
        * Programmatically reorder columns
        * 
        * @param from Column index to move.
        * @param to New index to move the column to.
        * @param drop Indicate that this is the final move. Set this to false if you are performing multiple moves
        * @param invalidate Invalidate the row data. As with drop it can be useful to set this to false if performing multiple moves. Otherwise allow it to default which will ensure that the table's data is fully insync with the column order.
        * @returns Unmodified API instance.
        */
        move(from: number, to: number, drop: boolean, invalidate: boolean): Api;

        /**
         * Get the current column order.
         * 
         * @returns Returns an array of column indexes. The column index given is the original column index, with its new position defined by the location in the returned array.
         */
        order(): Array<number>;

        /**
        * Set column order
        * 
        * @param newOrder Array of column indexes that define where the columns should be placed after the reorder.
        * @param originalIndexes Set to be true to indicate that the indexes passed in are the original indexes. false or undefined (default) will treat them as the current indexes.
        * @returns DataTables Api instance for chaining
        */
        order(newOrder: number[], originalIndexes?: boolean): Api;

        /**
        * Restore the loaded column order
        * 
        * @returns DataTables Api instance.
        */
        reset(): Api;

        /**
        * Convert one or more column indexes to and from current and original indexes
        * 
        * @param idx The index, or array of indexes to transpose.
        * @param direction Set what transposition is required. 
        * @returns The transpose values
        */
        transpose(idx: number | number[], direction?: "toCurrent" | "toOriginal" | "fromOriginal" | "fromCurrent"): Array<number>;
      };
    }
  }
  