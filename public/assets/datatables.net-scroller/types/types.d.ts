// Type definitions for DataTables Scroller
//
// Project: https://datatables.net/extensions/scroller/, https://datatables.net
// Definitions by:
//   SpryMedia
//   Konstantin Rohde <https://github.com/RohdeK>

import DataTables, {Api} from 'datatables.net';

export default DataTables;

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * DataTables' types integration
 */
declare module 'datatables.net' {
	interface Config {
		/**
		 * Scroller extension options
		 */
		scroller?: boolean | ConfigScroller;
	}

	interface Api<T> {
		/**
		 * Scroller methods container
		 * 
		 * @returns Api for chaining with the additional Scroller methods
		 */
		scroller: ApiScrollerMethods<T>;
	}

	interface ApiRowMethods<T> {
		/**
		 * Scroll to a row
		 */
		scrollTo(animate?: boolean): Api<T>;
	}

	interface DataTablesStatic {
		/**
		 * Scroller class
		 */
		Scroller: {
			/**
			 * Create a new Scroller instance for the target DataTable
			 */
			new (dt: Api<any>, settings: boolean | ConfigScroller): DataTablesStatic['Scroller'];

			/**
			 * Scroller version
			 */
			version: string;

			/**
			 * Default configuration values
			 */
			defaults: ConfigScroller;
		}
	}
}


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Options
 */

interface ConfigScroller {
	/**
	 * Scroller uses the boundary scaling factor to decide when to redraw the table - which it
	 * typically does before you reach the end of the currently loaded data set (in order to
	 * allow the data to look continuous to a user scrolling through the data).
	 */
	boundaryScale?: number;

	/**
	 * The display buffer is what Scroller uses to calculate how many rows it should pre-fetch
	 * for scrolling.
	 */
	displayBuffer?: number;

	/**
	 * Show (or not) the loading element in the background of the table. Note that you should
	 * include the dataTables.scroller.css file for this to be displayed correctly.
	 */
	loadingIndicator?: boolean;

	/**
	 * Scroller will attempt to automatically calculate the height of rows for it's internal
	 * calculations. However the height that is used can be overridden using this parameter.
	 */
	rowHeight?: number | string;

	/**
	 * When using server-side processing, Scroller will wait a small amount of time to allow
	 * the scrolling to finish before requesting more data from the server.
	 */
	serverWait?: number;
}


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * API
 */

interface ApiScrollerMethods<T> {
	/**
	 * Calculate and store information about how many rows are to be displayed
	 * in the scrolling viewport, based on current dimensions in the browser's
	 * rendering.
	 * 
	 * @param redraw Flag to indicate if the table should immediately redraw or not. true will redraw the table, false will not.
	 * @returns DataTables Api instance for chaining
	 */
	measure(redraw?: boolean): Api<T>;
	
	/**
	 * Get information about current displayed record range.
	 * 
	 * @returnsAn object with the parameters start and end, defining the start and end, 0 based, display indexes of the rows that are visible in the table's scrolling viewport.
	 */
	page(): PageInfo;

	/**
	 * Move the display to show the row at the index given.
	 * 
	 * @param index Display index to jump to.
	 * @param animate Animate the scroll (true) or not (false).
	 */
	toPosition(index: number, animate?: boolean): Api<T>;
}


interface PageInfo {
	/**
	 * The 0-indexed record at the top of the viewport
	 */
	start: number;

	/**
	 * The 0-indexed record at the bottom of the viewport
	 */
	end: number;
}
