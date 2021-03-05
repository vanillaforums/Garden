/**
 * @copyright 2009-2021 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */
import { cssOut } from "@dashboard/compatibilityStyles/cssOut";
import { metaContainerStyle, metaItemStyle, metaLinkItemStyle } from "@library/metas/Metas.styles";
import { important } from "csx";
import { metasVariables } from "@library/metas/Metas.variables";
import { tagVariables } from "@library/metas/Tag.variables";
import { Mixins } from "@library/styles/Mixins";
import { tagLinkStyle, tagStyle } from "@dashboard/compatibilityStyles/forumTagStyles";

export const metasCSS = () => {
    const vars = metasVariables();
    const tagVars = tagVariables();

    cssOut(`.Meta.Meta, .AuthorInfo`, {
        ...metaContainerStyle(),
        "& > .MItem": {
            ...metaItemStyle(),

            ...Mixins.padding({
                // make regular meta items have the same total height as meta tags
                vertical: tagVars.border.width,
            }),

            "&.IdeationTag": {
                //This element contains a .Tag, so it shouldn't get extra padding
                ...Mixins.padding({
                    all: 0,
                }),
            },

            "&.Hidden, &.RSS, &.JustNew": {
                display: "none",
            },
        },
        "& .Tag": {
            ...tagStyle(),
            background: "none",
        },
        "& a.Tag": {
            ...tagLinkStyle(),
            background: "none",
        },

        "& .MItem > .Tag": {
            ...Mixins.margin({
                all: 0,
            }),
        },
        "& > .MItem-Resolved": {
            width: 13,
            height: 14,
            padding: 0,
            marginBottom: 0,
            verticalAlign: "middle",
        },
    });

    cssOut(`.Meta.Meta .MItem a`, {
        ...metaLinkItemStyle(),
        display: "inline",
    });

    // FIXME: Once we resolve the absolute positioning in these cells
    // This won't be needed anymore.
    cssOut(".BlockColumn.BlockColumn", {
        "& .Meta > *": {
            marginLeft: 0,
            marginRight: (vars.spacing.horizontal! as number) * 2,
        },
    });

    // Special case for resolved
    cssOut(".resolved2-unresolved, .resolved2-resolved", {
        top: 0,
        display: "block",
    });

    // Special case for child categories in modern layout.
    // To see Modern Layout + "Discussions" type category with child categories.
    // Look at category list view.
    cssOut(".ChildCategories", {
        ...Mixins.margin({
            horizontal: vars.spacing.horizontal,
        }),
    });
};
