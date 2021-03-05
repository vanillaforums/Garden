/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React, { useLayoutEffect, useRef, useState } from "react";
import { containerClasses } from "@library/layout/components/containerStyles";
import classNames from "classnames";
import { ISpacing } from "@library/styles/cssUtilsTypes";
import { cx } from "@emotion/css";

export interface IContainer {
    className?: string;
    children?: React.ReactNode;
    tag?: keyof JSX.IntrinsicElements;
    fullGutter?: boolean; // Use when a component wants a full mobile/desktop gutter.
    // Useful for components that don't provide their own padding.
    narrow?: boolean;
    style?: object;
    maxWidth?: number;
    gutterSpacing?: ISpacing;
}

/*
 * Implements "Container" component used to set max width of content of page.
 */
export const Container = React.forwardRef(function Container(props: IContainer, ref: React.RefObject<HTMLElement>) {
    const { tag, children, className, fullGutter = false, narrow = false, style = {}, maxWidth, gutterSpacing } = props;
    const classes = containerClasses({ maxWidth, desktopSpacing: gutterSpacing });
    const ownRef = useRef<HTMLElement>(null);
    ref = ref ?? ownRef;

    const [hasParentContainer, setHasParentContainer] = useState(false);
    useLayoutEffect(() => {
        // Sometimes when mounting a legacy component we can end up inside of a legacy container that already gives us a gutter.
        // In those cases, we don't actually want to render another one.
        const closestParent = ref.current?.closest(".Container");
        if (closestParent instanceof HTMLElement) {
            setHasParentContainer(true);
        }
        // if (ref.current?.)
    }, [ref]);

    if (hasParentContainer) {
        return <>{children}</>;
    }

    if (children) {
        const Tag = tag || "div";
        return (
            <Tag
                ref={ref}
                style={style}
                className={cx(
                    classes.root,
                    {
                        [classes.fullGutter]: fullGutter,
                        isNarrow: narrow,
                    },
                    // Last it takes precedences
                    className,
                )}
            >
                {children}
            </Tag>
        );
    } else {
        return null;
    }
});

export default Container;
