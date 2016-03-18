<?php

namespace Jade\Compiler;

use Jade\Nodes\Tag;

abstract class TagVisitor extends Visitor
{
    /**
     * @param Nodes\Tag $tag
     */
    protected function visitTagAttributes(Tag $tag, $close = '>')
    {
        $open = '<' . $tag->name;

        if (count($tag->attributes)) {
            $this->buffer($this->indent() . $open, false);
            $this->visitAttributes($tag->attributes);
            $this->buffer($close . $this->newline(), false);

            return;
        }

        $this->buffer($open . $close);
    }

    /**
     * @param Nodes\Tag $tag
     */
    protected function initTagName(Tag $tag)
    {
        if (isset($tag->buffer)) {
            if (preg_match('`^[a-z][a-zA-Z0-9]+(?!\()`', $tag->name)) {
                $tag->name = '$' . $tag->name;
            }
            $tag->name = trim($this->createCode('echo ' . $tag->name . ';'));
        }
    }

    /**
     * @param Nodes\Tag $tag
     */
    protected function visitTagContents(Tag $tag)
    {
        $this->indents++;
        if (isset($tag->code)) {
            $this->visitCode($tag->code);
        }
        $this->visit($tag->block);
        $this->indents--;
    }

    /**
     * @param Nodes\Tag $tag
     */
    protected function compileTag(Tag $tag)
    {
        $selfClosing = (in_array(strtolower($tag->name), $this->selfClosing) || $tag->selfClosing) && !$this->xml;
        $this->visitTagAttributes($tag, (!$selfClosing || $this->terse) ? '>' : ' />');

        if (!$selfClosing) {
            $this->visitTagContents($tag);
            $this->buffer('</' . $tag->name . '>');
        }
    }

    /**
     * @param Nodes\Tag $tag
     */
    protected function visitTag(Tag $tag)
    {
        $this->initTagName($tag);

        $prettyprint = (
            $tag->keepWhiteSpaces() ||
            (!$tag->canInline() && $this->prettyprint && !$tag->isInline())
        );

        $this->tempPrettyPrint($prettyprint, 'compileTag', $tag);

        if (!$prettyprint && $this->prettyprint && !$tag->isInline()) {
            $this->buffer($this->newline());
        }
    }
}