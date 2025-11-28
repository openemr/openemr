module.exports = {
  darkMode: 'class',
  content: [
    "./src/**/*.{jsx,ts,tsx}",
  ],
  theme: {
    extend: {
      fontFamily: {
        sfuidisplay: ["SFUIDisplay"],
        sfuidisplayBlack: ["SFUIDisplayBlack"],
      },
      transitionProperty: {
        'width': 'width'
    },
      colors:{
        'primaryColor': '#ce3d47',
        'primaryRedBgColor':'#f2e4e5',
        'primaryRedColor':'#e5999e',
        'secondaryColor':'#bec1c5',
        'primaryRedDeepColor':'#F57880'
      },
    },
    
    
  },
  plugins: [
    require('@tailwindcss/line-clamp'),
  ],
}
