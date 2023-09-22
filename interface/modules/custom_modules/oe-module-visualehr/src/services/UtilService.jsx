import { format } from "date-fns";
import { YEARS, MONTHS } from "../utilities/constants";

export const UtilService = {
  getPid: () => {
    // var params = new URLSearchParams(window.location.search);
    // return params.get("pid") !== "" && params.get("pid") !== null
    //     ? params.get("pid") : 1;
    return getCookie("pid_vehr") !== "" && getCookie("pid_vehr") !== null
      ? getCookie("pid_vehr")
      : 1;
  },
  toString: (text) => {
    if (text !== "") {
      return text;
    } else {
      return "";
    }
  },
  isNotEqual: (a, b) => {},
  toStringNull: (text) => {
    if (text !== "" && text !== null) {
      return text;
    } else {
      return null;
    }
  },
  currentDate: () => {
    return format(new Date(), "yyyy-MM-dd");
  },
  nextYearCalendar: () => {
    return parseInt(format(new Date(), "yyyy")) + 1 + "-01-01";
  },
  nextYear: () => {
    return parseInt(format(new Date(), "yyyy")) + 1;
  },
  zoomSpeed: 150,
  mobileScreen: 700,
  zoomMinWidth: (size) => {
    return 300 * size;
  },
  containerMargin: (setMargin, screenSize) => {
    return setMargin ? 70 : screenSize <= UtilService.mobileScreen ? 50 : 224;
  },
  defaultWidth: 1800,
  tilesWidth: (size) => {
    return size > 5
      ? UtilService.zoomMinWidth(1)
      : UtilService.defaultWidth / size;
  },
  containerWidth: (size) => {
    return size > 5
      ? UtilService.zoomMinWidth(1) * size
      : UtilService.defaultWidth;
  },
  serializeEndDate: (enddate, viewType) => {
    return serializeEndDate(enddate, viewType);
  },
  handleIsInDateRange: (start, end, date, viewType) => {
    return handleIsInDateRange(start, end, date, viewType);
  },
  serializeDate: (date) => {
    return serializeDate(date);
  },
  hasTitleShow: (endate, date) => {
    return hasTitleShow(endate, date);
  },
  makeKey: (length) => {
    return makeKeyString(length);
  },
};
const serializeDate = (date) => {
  if (data === null || date === undefined || date === "")
    return format(new Date(), "yyyy-MM-dd");
  var data = date.split(" ");
  var datetime = data.length == 2 ? data[0] : date;
  return format(new Date(datetime), "yyyy-MM-dd");
};
const getCookie = (name) => {
  var nameEQ = name + "=";
  var ca = document.cookie.split(";");
  for (var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) === " ") c = c.substring(1, c.length);
    if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
  }
  return null;
};

const serializeEndDate = (enddate, viewType) => {
  var edate = parseInt(format(new Date(enddate), "yyyy"));
  if (edate + 1 === UtilService.nextYear() && viewType === YEARS) {
    UtilService.nextYear();
  }
  return enddate;
};
const hasTitleShow = (endate, date) => {
  return endate.startsWith(date);
};
const handleIsInDateRange = (start, end, date, viewType) => {
  if (end === null || date === null) return false;

  if (viewType === YEARS) {
    var check = parseInt(date);
    var sdate = parseInt(format(new Date(start), "yyyy"));
    var edate = parseInt(format(new Date(end), "yyyy"));
    return (
      sdate <= check && (edate >= check || edate + 1 === UtilService.nextYear())
    );
  } else {
    var dateformat = viewType === MONTHS ? "yyyy-MM" : "yyyy-MM-dd";
    var check = handleSerializeDate(date);
    var sdate = handleSerializeDate(format(new Date(start), dateformat));
    var edate = handleSerializeDate(format(new Date(end), dateformat));
    var enddate = parseInt(format(new Date(end), "yyyy"));
    return (
      sdate <= check &&
      (edate >= check || enddate + 1 === UtilService.nextYear())
    );
  }
};
const handleSerializeDate = (date) => {
  var data = date.split("-");
  if (data.length === 2) {
    return new Date(data[0], data[1]).getTime();
  } else if (data.length === 3) {
    return new Date(data[0], data[1], data[2]).getTime();
  }
};

const makeKeyId = () => {
  const min = 1;
  const max = 100;
  return min + Math.random() * (max - min);
};
const makeKeyString = (length) => {
  var result = "";
  var characters =
    "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
  var charactersLength = characters.length;
  for (var i = 0; i < length; i++) {
    result += characters.charAt(Math.floor(Math.random() * charactersLength));
  }
  return result;
};
