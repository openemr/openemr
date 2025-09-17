class Range
    constructor: (@trie) ->
        if @trie.constructor != Trie
            throw Error 'Range constructor requires a Trie parameter'

    @rangeWithString: (ranges) ->
        if typeof ranges != 'string'
            throw Error 'rangeWithString requires a string parameter'

        ranges = ranges.replace(/ /g, '')
        ranges = ranges.split ','

        trie = new Trie

        for range in ranges
            if r = range.match /^(\d+)-(\d+)$/
                for n in [r[1]..r[2]]
                    trie.push n
            else if range.match /^\d+$/
                trie.push range
            else
                throw Error "Invalid range '#{r}'"

        new Range trie

    match: (number) ->
        return @trie.find(number)