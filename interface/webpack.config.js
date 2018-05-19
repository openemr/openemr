module.exports = (env = {}) => {
    return {
        entry: ['./themes/style_light.scss'],
        output: {
            filename: 'assets/js/bundle.js',
        },
        module: {
            rules: [
                {
                    test: /\.scss$/,
                    use: [
                        {
                            loader: 'file-loader',
                            options: {
                                name: '[name].css',
                                outputPath: 'assets/css/'
                            }
                        },
                        {
                            loader: 'extract-loader'
                        },
                        {
                            loader: 'css-loader'
                        },
                        {
                            loader: 'sass-loader'
                        }
                    ]
                },
                { test: /\.(php|gif|jpg|ttf|png)$/, loader: 'ignore-loader' }
            ]
        }
    }
};
