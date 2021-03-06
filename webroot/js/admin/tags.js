/*!
 * This file is part of me-cms.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/me-cms
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
$(function () {
    //Input button to add tags
    var inputButton = $("#tags-input-button");

    //Input text
    var inputText = $("#tags-input-text");

    //Output text
    var outputText = $("#tags-output-text");

    //Preview wrapper
    var preview = $("#tags-preview");

    //Sets the counter, index for tags
    var counter = 1;

    //List of tags
    var listOfTags = [];

    /**
     * Checks if a tag already exists
     * @param string tag Tag value
     * @returns bool
     */
    function tagExists(tag)
    {
        var listOfTagsLength = listOfTags.length;

        for (var i = 0; i < listOfTagsLength; i++) {
            if (listOfTags[i].value === tag) {
                return true;
            }
        }

        return false;
    }

    /**
     * Adds tags
     * @param array tags Tags
     */
    function addTags(tags)
    {
        $.each(tags, function (index, tag) {
            //Checks for length
            if (tag.length < 3) {
                return;
            }

            //Returns, if the tag already exists
            if (tagExists(tag)) {
                return;
            }

            tag = tag.toLowerCase(); //Lowercase

            //Changes invalid chars
            var from = "àáäâèéëêìíïîıòóöôùúüûñç·/_,:;-";
            var to = "aaaaeeeeiiiiioooouuuunc       ";
            var fromLength = from.length;
            for (var i = 0; i < fromLength; i++) {
                tag = tag.replace(new RegExp(from.charAt(i), "g"), to.charAt(i));
            }

            //Removes invalid chars
            tag = tag.replace(/[^a-z0-9\s]/g, "")

            //Pushes the tag on the list of tags
            listOfTags.push({key:counter, value:tag});

            //Appends HTML
            var closeButton = "<button type=\"button\" data-tag=\"" + counter + "\" class=\"tag-remove\" href=\"#\">&times;</button>";
            preview.append("<div class=\"tag\" data-tag=\"" + counter + "\">" + tag + closeButton + "</div>");

            //Increments the counter
            counter++;
        });

        //At the end, updates the output text
        updateOutputText();
    }

    /**
     * Removes a tag
     * @param string|int id Tag id
     */
    function removeTag(id)
    {
        var listOfTagsLength = listOfTags.length;

        for (var i = 0; i < listOfTagsLength; i++) {
            if (listOfTags[i].key === parseInt(id)) {
                //Removes the tag from the list of tag
                listOfTags.splice(i, 1);

                //Removes HTML
                $("div[data-tag=" + id + "]", preview).remove();

                break;
            }
        }

        //At the end, updates the output text
        updateOutputText();
    }

    /**
     * Updates the output text
     */
    function updateOutputText()
    {
        //Creates a new empty array
        var newTags = [];

        $.each(listOfTags, function (index, tag) {
            //Pushes only the tag values the new array with
            newTags.push(tag["value"]);
        });

        //Updats the output text
        outputText.val(newTags.join(", "));
    }

    //On start, gets and adds tags from the output text
    addTags(outputText.val().split(", "));

    //On click on the input button
    inputButton.click(function () {
        //Gets and trims the input value
        var inputValue = inputText.val().replace(/^\s+|\s+$/g, "");

        //Checks for length
        if (!inputValue.length) {
            return false;
        }

        //Resets the input text
        inputText.val("");

        //Adds tags
        addTags(inputValue.split(/\s*,+\s*/));
    });

    //On click on the remove link
    $(preview).on("click", ".tag-remove", function () {
        //Removes the tag
        removeTag($(this).attr("data-tag"));
    });

    //On focus on the input text
    $(inputText).on("focusin", function () {
        $(this).off("keydown").keydown(function (event) {
            //On press the "enter" button
            if (event.keyCode === 13) {
                //Prevent default event
                event.preventDefault();

                //Clicks on input button
                inputButton.click();

                return false;
            }
        });
    });
});