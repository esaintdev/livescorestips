import 'package:flutter/material.dart';

import '../core/app_colors.dart';

/// Continuous near-black page with a lightweight, integrated top navigation
/// (back arrow + title) — no filled header bar. Content scrolls under the
/// keyboard and respects the safe area.
class AuthScaffold extends StatelessWidget {
  const AuthScaffold({super.key, required this.title, required this.children});

  final String title;
  final List<Widget> children;

  @override
  Widget build(BuildContext context) {
    final bool dark = Theme.of(context).brightness == Brightness.dark;
    final Color bg = dark ? AppColors.darkBackground : AppColors.lightBackground;
    final Color text = dark ? AppColors.darkTextBody : AppColors.lightText900;

    return Scaffold(
      backgroundColor: bg,
      body: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(6, 2, 16, 0),
              child: Row(
                children: [
                  IconButton(
                    onPressed: () => Navigator.of(context).maybePop(),
                    icon: const Icon(Icons.arrow_back, size: 22),
                    color: text,
                    splashRadius: 22,
                    tooltip: 'Back',
                  ),
                  const SizedBox(width: 2),
                  Text(
                    title,
                    style: TextStyle(
                      fontSize: 17,
                      fontWeight: FontWeight.w700,
                      color: text,
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.fromLTRB(24, 12, 24, 24),
                keyboardDismissBehavior:
                    ScrollViewKeyboardDismissBehavior.onDrag,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: children,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// Small, restrained brand mark (the app favicon).
class AuthBrandMark extends StatelessWidget {
  const AuthBrandMark({super.key, this.size = 30});

  final double size;

  @override
  Widget build(BuildContext context) {
    return Image.asset(
      'assets/images/icon.png',
      width: size,
      height: size,
      errorBuilder: (context, error, stack) =>
          Icon(Icons.sports_soccer, size: size, color: AppColors.accent),
    );
  }
}

/// Editorial headline + supporting copy, left-aligned.
class AuthHeading extends StatelessWidget {
  const AuthHeading({super.key, required this.headline, required this.supporting});

  final String headline;
  final String supporting;

  @override
  Widget build(BuildContext context) {
    final bool dark = Theme.of(context).brightness == Brightness.dark;
    final Color titleColor = dark ? const Color(0xFFF6F6F6) : AppColors.lightText900;
    final Color subColor =
        dark ? AppColors.darkTextMuted : AppColors.lightText500;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const AuthBrandMark(),
        const SizedBox(height: 18),
        Text(
          headline,
          style: TextStyle(
            fontSize: 26,
            fontWeight: FontWeight.w800,
            height: 1.15,
            letterSpacing: -0.3,
            color: titleColor,
          ),
        ),
        const SizedBox(height: 8),
        Text(
          supporting,
          style: TextStyle(fontSize: 14, height: 1.45, color: subColor),
        ),
      ],
    );
  }
}

/// Slim labelled input: subtle 1px border, orange on focus, red on error,
/// outline left icon, optional password visibility toggle.
class AuthField extends StatefulWidget {
  const AuthField({
    super.key,
    required this.label,
    required this.icon,
    required this.controller,
    this.hint,
    this.isPassword = false,
    this.keyboardType = TextInputType.text,
    this.textInputAction = TextInputAction.next,
    this.textCapitalization = TextCapitalization.none,
    this.autofillHints,
    this.validator,
    this.onSubmitted,
  });

  final String label;
  final IconData icon;
  final TextEditingController controller;
  final String? hint;
  final bool isPassword;
  final TextInputType keyboardType;
  final TextInputAction textInputAction;
  final TextCapitalization textCapitalization;
  final Iterable<String>? autofillHints;
  final String? Function(String?)? validator;
  final ValueChanged<String>? onSubmitted;

  @override
  State<AuthField> createState() => _AuthFieldState();
}

class _AuthFieldState extends State<AuthField> {
  bool _obscure = true;

  @override
  Widget build(BuildContext context) {
    final bool dark = Theme.of(context).brightness == Brightness.dark;
    final Color labelColor =
        dark ? AppColors.darkTextMuted : AppColors.lightText400;
    final Color fill =
        dark ? const Color(0xFF141414) : AppColors.lightSurfaceMid;
    final Color textColor =
        dark ? const Color(0xFFF2F2F2) : AppColors.lightText900;
    final Color hintColor =
        dark ? const Color(0xFF6E6E6E) : AppColors.lightText200;
    final Color iconColor =
        dark ? const Color(0xFF8A8A8A) : AppColors.lightText400;
    final Color border =
        dark ? Colors.white.withValues(alpha: 0.10) : AppColors.lightBorder();

    const BorderRadius radius = BorderRadius.all(Radius.circular(12));

    OutlineInputBorder b(Color c, [double w = 1]) => OutlineInputBorder(
          borderRadius: radius,
          borderSide: BorderSide(color: c, width: w),
        );

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          widget.label,
          style: TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w600,
            letterSpacing: 0.2,
            color: labelColor,
          ),
        ),
        const SizedBox(height: 8),
        TextFormField(
          controller: widget.controller,
          obscureText: widget.isPassword ? _obscure : false,
          keyboardType: widget.keyboardType,
          textInputAction: widget.textInputAction,
          textCapitalization: widget.textCapitalization,
          autofillHints: widget.autofillHints,
          validator: widget.validator,
          onFieldSubmitted: widget.onSubmitted,
          cursorColor: AppColors.accent,
          style: TextStyle(fontSize: 15, color: textColor),
          decoration: InputDecoration(
            isDense: true,
            filled: true,
            fillColor: fill,
            hintText: widget.hint,
            hintStyle: TextStyle(fontSize: 14.5, color: hintColor),
            contentPadding:
                const EdgeInsets.symmetric(vertical: 16, horizontal: 12),
            prefixIcon: Icon(widget.icon, size: 19, color: iconColor),
            prefixIconConstraints:
                const BoxConstraints(minWidth: 44, minHeight: 20),
            suffixIcon: widget.isPassword
                ? IconButton(
                    onPressed: () => setState(() => _obscure = !_obscure),
                    icon: Icon(
                      _obscure
                          ? Icons.visibility_off_outlined
                          : Icons.visibility_outlined,
                      size: 19,
                    ),
                    color: iconColor,
                    splashRadius: 20,
                    tooltip: _obscure ? 'Show password' : 'Hide password',
                  )
                : null,
            border: b(border),
            enabledBorder: b(border),
            focusedBorder: b(AppColors.accent, 1.5),
            errorBorder: b(AppColors.danger),
            focusedErrorBorder: b(AppColors.danger, 1.5),
            errorStyle:
                const TextStyle(fontSize: 11.5, color: AppColors.danger),
          ),
        ),
      ],
    );
  }
}

/// Full-width primary CTA — orange, dark text, moderate radius.
class AuthPrimaryButton extends StatelessWidget {
  const AuthPrimaryButton({
    super.key,
    required this.label,
    required this.onPressed,
    this.busy = false,
  });

  final String label;
  final VoidCallback? onPressed;
  final bool busy;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: double.infinity,
      height: 56,
      child: ElevatedButton(
        onPressed: busy ? null : onPressed,
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.accent,
          foregroundColor: const Color(0xFF160A00),
          disabledBackgroundColor: AppColors.accent.withValues(alpha: 0.55),
          elevation: 0,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
        ),
        child: busy
            ? const SizedBox(
                width: 20,
                height: 20,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  color: Color(0xFF160A00),
                ),
              )
            : Text(
                label,
                style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w800,
                ),
              ),
      ),
    );
  }
}

/// Centered secondary action: muted prefix + orange action label.
class AuthSecondaryLink extends StatelessWidget {
  const AuthSecondaryLink({
    super.key,
    required this.prefix,
    required this.action,
    required this.onTap,
  });

  final String prefix;
  final String action;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final bool dark = Theme.of(context).brightness == Brightness.dark;
    final Color muted = dark ? AppColors.darkTextMuted : AppColors.lightText500;

    return SizedBox(
      width: double.infinity,
      child: Wrap(
        alignment: WrapAlignment.center,
        crossAxisAlignment: WrapCrossAlignment.center,
        children: [
          Text(prefix, style: TextStyle(fontSize: 13, color: muted)),
          GestureDetector(
            onTap: onTap,
            behavior: HitTestBehavior.opaque,
            child: Text(
              action,
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w700,
                color: AppColors.accent,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

/// Small custom checkbox with an orange fill when selected.
class AuthCheckbox extends StatelessWidget {
  const AuthCheckbox({super.key, required this.value, required this.onChanged, required this.label});

  final bool value;
  final ValueChanged<bool> onChanged;
  final String label;

  @override
  Widget build(BuildContext context) {
    final bool dark = Theme.of(context).brightness == Brightness.dark;
    final Color muted = dark ? AppColors.darkTextSecondary : AppColors.lightText500;
    final Color border =
        dark ? Colors.white.withValues(alpha: 0.28) : AppColors.lightBorder(0.3);

    return GestureDetector(
      behavior: HitTestBehavior.opaque,
      onTap: () => onChanged(!value),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          AnimatedContainer(
            duration: const Duration(milliseconds: 150),
            width: 20,
            height: 20,
            decoration: BoxDecoration(
              color: value ? AppColors.accent : Colors.transparent,
              borderRadius: BorderRadius.circular(6),
              border: Border.all(
                color: value ? AppColors.accent : border,
              ),
            ),
            child: value
                ? const Icon(Icons.check, size: 14, color: Color(0xFF160A00))
                : null,
          ),
          const SizedBox(width: 9),
          Text(label, style: TextStyle(fontSize: 13, color: muted)),
        ],
      ),
    );
  }
}
