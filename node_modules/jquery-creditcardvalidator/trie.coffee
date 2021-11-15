class Trie
    constructor: ->
        @trie = {}

    push: (value) ->
        value = value.toString()

        obj = @trie

        for char, i in value.split('')
            if not obj[char]?
                if i == (value.length - 1)
                    obj[char] = null
                else
                    obj[char] = {}

            obj = obj[char]

    find: (value) ->
        value = value.toString()

        obj = @trie

        for char, i in value.split('')
            if obj.hasOwnProperty char
                if obj[char] == null
                    return true
            else
                return false

            obj = obj[char]